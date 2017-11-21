## Install

**Composer:**

```
composer require youshido/graphql-bundle
```

**AppKernel:**

```
new Youshido\GraphQLBundle\GraphQLBundle(),
new Kunstmaan\ApiBundle\KunstmaanApiBundle()
```

**routing.yml:**

```
kunstmaan_api:
    path: /api
    defaults:
        _controller: KunstmaanApiBundle:Api:api

```

## TODO

- [ ] Sub query problems
![Error](https://i.imgur.com/dordEh5.jpg)
```
query {
  groupList {
    id
    name
    roles{
			id
			role
    }
  }
}
```
```
query {
  node {
    id
    internalName
    refEntityName
    nodeTranslations {
      id
      title
      slug
      lang
    }
  }
}

```
- [ ] Mutations ( simple and advanced for e.g. node)
- [ ] Refactor some parts of code
- [ ] Add annotations to Entity
- [ ] Security - token
- [ ] Dynamic schema
- [ ] Event Dispatcher for Schema
