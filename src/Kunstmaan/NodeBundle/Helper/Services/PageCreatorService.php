<?php

namespace Kunstmaan\NodeBundle\Helper\Services;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Kunstmaan\AdminBundle\Repository\UserRepository;
use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Repository\NodeRepository;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\SeoBundle\Entity\Seo;
use Kunstmaan\SeoBundle\Repository\SeoRepository;

/**
 * Service to create new pages.
 */
class PageCreatorService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ACLPermissionCreatorService
     */
    private $aclPermissionCreatorService;

    /**
     * @var string
     */
    private $userEntityClass;

    /**
     * PageCreatorService constructor.
     * @param EntityManagerInterface $entityManager
     * @param ACLPermissionCreatorService $aclPermissionCreatorService
     * @param $userEntityClass
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ACLPermissionCreatorService $aclPermissionCreatorService,
        $userEntityClass
    )
    {
        $this->entityManager                = $entityManager;
        $this->aclPermissionCreatorService  = $aclPermissionCreatorService;
        $this->userEntityClass              = $userEntityClass;
    }

    /**
     * @param HasNodeInterface $pageTypeInstance The page.
     * @param array            $translations     Containing arrays. Sample:
     * [
     *  [   "language" => "nl",
     *      "callback" => function($page, $translation) {
     *          $translation->setTitle('NL titel');
     *      }
     *  ],
     *  [   "language" => "fr",
     *      "callback" => function($page, $translation) {
     *          $translation->setTitle('FR titel');
     *      }
     *  ]
     * ]
     * Perhaps it's cleaner when you create one array and append another array for each language.
     *
     * @param array            $options          Possible options:
     *      parent: type node, nodetransation or page.
     *      page_internal_name: string. name the page will have in the database.
     *      set_online: bool. if true the page will be set as online after creation.
     *      hidden_from_nav: bool. if true the page will not be show in the navigation
     *      creator: username
     *
     * Automatically calls the ACL + sets the slugs to empty when the page is an Abstract node.
     *
     * @return Node The new node for the page.
     *
     * @throws \InvalidArgumentException
     */
    public function createPage(HasNodeInterface $pageTypeInstance, array $translations, array $options = [])
    {
        if (empty($translations)) {
            throw new \InvalidArgumentException('There has to be at least 1 translation in the translations array');
        }
        
        /** @var NodeRepository $nodeRepo */
        $nodeRepo = $this->entityManager->getRepository(Node::class);
        
        $pagecreator        = array_key_exists('creator', $options) ? $options['creator'] : 'pagecreator';
        $creator            = $this->entityManager->getRepository($this->userEntityClass)->findOneBy(['username' => $pagecreator]);
        $parent             = isset($options['parent']) ? $options['parent'] : null;
        $pageInternalName   = isset($options['page_internal_name']) ? $options['page_internal_name'] : null;
        $setOnline          = isset($options['set_online']) ? $options['set_online'] : false;

        // We need to get the language of the first translation so we can create the rootnode.
        // This will also create a translationnode for that language attached to the rootnode.
        $first    = true;
        $rootNode = null;

        /* @var \Kunstmaan\NodeBundle\Repository\NodeTranslationRepository $nodeTranslationRepo*/
        $nodeTranslationRepo = $this->entityManager->getRepository(NodeTranslation::class);

        foreach ($translations as $translation) {
            $language = $translation['language'];
            $callback = $translation['callback'];

            $translationNode = null;
            if ($first) {
                $first = false;

                $this->entityManager->persist($pageTypeInstance);
                $this->entityManager->flush();

                // Fetch the translation instead of creating it.
                // This returns the rootnode.
                $rootNode = $nodeRepo->createNodeFor($pageTypeInstance, $language, $creator, $pageInternalName);

                if (array_key_exists('hidden_from_nav', $options)) {
                    $rootNode->setHiddenFromNav($options['hidden_from_nav']);
                }

                if (null !== $parent) {
                    if ($parent instanceof HasPagePartsInterface) {
                        $parent = $nodeRepo->getNodeFor($parent);
                    }
                    $rootNode->setParent($parent);
                }

                $this->entityManager->persist($rootNode);
                $this->entityManager->flush();

                $translationNode = $rootNode->getNodeTranslation($language, true);
            } else {
                // Clone the $pageTypeInstance.
                $pageTypeInstance = clone $pageTypeInstance;

                $this->entityManager->persist($pageTypeInstance);
                $this->entityManager->flush();

                // Create the translationnode.
                $translationNode = $nodeTranslationRepo->createNodeTranslationFor($pageTypeInstance, $language, $rootNode, $creator);
            }

            // Make SEO.
            $seo = $this->entityManager->getRepository(Seo::class)->findOrCreateFor($pageTypeInstance);

            $callback($pageTypeInstance, $translationNode, $seo);

            // Overwrite the page title with the translated title
            $pageTypeInstance->setTitle($translationNode->getTitle());

            $this->entityManager->persist($pageTypeInstance);
            $this->entityManager->flush();

            $this->entityManager->persist($translationNode);
            $this->entityManager->flush();

            $translationNode->setOnline($setOnline);

            if (null !== $seo) {
                $this->entityManager->persist($seo);
                $this->entityManager->flush();
            }

            $this->entityManager->persist($translationNode);
            $this->entityManager->flush();
        }

        // ACL
        $this->aclPermissionCreatorService->createPermission($rootNode);

        return $rootNode;
    }

}
