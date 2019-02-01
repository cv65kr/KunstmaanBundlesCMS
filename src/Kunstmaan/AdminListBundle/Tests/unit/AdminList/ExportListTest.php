<?php

namespace Kunstmaan\AdminListBundle\Tests\AdminList;

use ArrayIterator;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Iterator;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractAdminListConfigurator;
use Kunstmaan\AdminListBundle\AdminList\ExportList;
use Kunstmaan\AdminListBundle\AdminList\Field;
use Kunstmaan\FormBundle\AdminList\FormSubmissionExportListConfigurator;
use Kunstmaan\FormBundle\Entity\FormSubmission;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\TranslatorBundle\Service\Translator\Translator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class ExportListTest extends TestCase
{
    /**
     * @var ExportList
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurator;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())->method('iterate')->willReturn(new ArrayIterator([[new FormSubmission()]]));

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->any())->method('select')->willReturn($qb);
        $qb->expects($this->any())->method('from')->willReturn($qb);
        $qb->expects($this->any())->method('innerJoin')->willReturn($qb);
        $qb->expects($this->any())->method('andWhere')->willReturn($qb);
        $qb->expects($this->any())->method('setParameter')->willReturn($qb);
        $qb->expects($this->any())->method('addOrderBy')->willReturn($qb);
        $qb->expects($this->any())->method('getQuery')->willReturn($query);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())->method('createQueryBuilder')->willReturn($qb);

        $node = new Node();
        $node->setId(666);
        $nodeTranslation = $this->createMock(NodeTranslation::class);
        $nodeTranslation->expects($this->any())->method('getNode')->willReturn($node);
        $nodeTranslation->expects($this->any())->method('getLang')->willReturn('nl');
        $translator = $this->createMock(Translator::class);

        $this->configurator = new FormSubmissionExportListConfigurator($em, $nodeTranslation, $translator); //$this->createMock('Kunstmaan\AdminListBundle\AdminList\Configurator\ExportListConfiguratorInterface');

        $this->object = new ExportList($this->configurator);
    }

    public function testGetExportColumns()
    {
        $columns = $this->object->getExportColumns();
        $this->assertCount(3, $columns);
        $this->assertInstanceOf(Field::class, $columns[0]);
        $this->assertInstanceOf(Field::class, $columns[1]);
        $this->assertInstanceOf(Field::class, $columns[2]);
    }

    public function testGetIterator()
    {
        $this->assertInstanceOf(Iterator::class, $this->object->getIterator());
    }

    public function testGetStringValue()
    {
        $item = array('id' => 1);

        $this->assertEquals(1, $this->object->getStringValue($item, 'id'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testBindRequest()
    {
        $configurator = $this->createMock(AbstractAdminListConfigurator::class);
        $configurator->expects($this->once())->method('bindRequest')->willReturn(true);
        $mirror = new ReflectionClass(ExportList::class);
        $property = $mirror->getProperty('configurator');
        $property->setAccessible(true);
        $property->setValue($this->object, $configurator);
        $this->object->bindRequest(new Request());
    }
}
