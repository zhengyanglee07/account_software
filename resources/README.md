## Table of Contents
- [Folder Structure](#folder-structure)
  - [pages](#pages)
  - [assets](#assets)
  - [components](#components)
  - [layouts](#layouts)
  - [lib](#lib)
  - [mixins](#mixins)
  - [plugins](#plugins)
  - [store](#store)
- [Register global component](#register-global-vue-component)

## Folder Structure

### pages
- directory for all the page vue components in your module
- all the sub components inside a page vue component should be registered under `components/`
- Naming Convention: `PascalCase`
  - Exp: `DomainSettings.vue`

### assets
- contains static files such as media, css and js (non-executable)
- Naming Convention: `camelCase`
  - Exp: `fontFamilies.js`

### components
- contains all the reusable vue components
- we will use **flat component directory** here, which means we will always make components directory 1-level deep only, no need to seperate components into different directory
  - ![Flat component directory](../../docs/img/flat-components-directory.jpg)
  - Advantages of flat components directory ([Full Reference](https://vueschool.io/articles/vuejs-tutorials/how-to-structure-a-large-scale-vue-js-application/)):
    1. Simplify importing components
       ```
        # Instead of:
        import SomeComponent from "@module/components/some/annoying/nested/directory/SomeComponent.vue"

        # Simply do:
        import SomeComponent from "@module/components/SomeComponent.vue"
       ```
    1. Remove analysis paralysis when it comes to deciding how to organize components into sub directories
    1. Use your IDE's quick find or file jumping feature to filter files based on their most general attribute down to the more specific
    1. Eliminate surfing the file structure in and out of directories to find a component
    1. Quickly and easily go from spotting a component in Vue devtools to finding the file in the codebase (the filename and the component name are the same)
- Naming Convention: 
  - `PascalCase`
  - always prefix the component with the name of the category it belongs to
    - Exp: 
      - For a card component in Apps page - `AppCard`
      - For a layout component in Settings page - `SettingLayout.vue`

### layouts
- contains vue component that will be the layout in your module's page
- the component here should just define the layout of the page. Complex content and logics not related to layout should be extract to their dedicated component in `components/` 
- now all pages in Hypershapes will use `@shared/layout/BaseLayout.vue` as default layout component
- To use a different layout component in your inertia page (Inertia not supporting layout declaration in script setup):
    ```
    import OtherLayoutComponent from '@layouts/OtherLayoutComponent.vue';

    export default {
        layout: OtherLayoutComponent,
    }
    ```
- Naming Convention: 
  - `PascalCase`
  - always append the component name with `Layout`
    - Exp:
      - `BaseLayout.vue`
      - `OnboardingLayout.vue`

### lib
- contains reusable input-output kind of helper functions for your module
- Naming Convention: `camelCase`

### mixins
- contains all the mixin files used in your module
- Naming Convention: 
  - `camelCase`
  - always append the mixin file name with `Mixin`
    - Exp: 
      - `stripePaymentMixin.js`
      - `verifySenderDomainMixin.js`
> Since we have switched to Vue 3, it is recommended to use composable instead of mixin now

### plugins
- contains the setup for all the Vue-related third-party packages and libraries

### store
- contains [Vuex](https://vuex.vuejs.org/) store for your module
  - Exp:
  ```
   module
    |-> store
      |-> actions.js
      |-> getters.js
      |-> index.js
      |-> mutations.js
      |-> state.js
  ```
- Naming Convention for module: `camelCase`

## Register global vue component
1. navigate to the `resources/app.js` file
1. import your component asynchronously
   - always make sure the name you import same as the component name for consistency
   - always include file extension of `.vue` while importing your component
     ```
      const CurrencySelect = defineAsyncComponent(() =>
        import('@shared/components/CurrencySelect.vue')
      );
      ``` 
1. register your component under import
    ```
     app.component({component name}, {component name})
    ```
    - always make sure the component name is PascalCase while importing and registering it

Full example:
```
//* Order
const CurrencySelect = defineAsyncComponent(() =>
  import('@shared/components/CurrencySelect.vue')
);
app.component('CurrencySelect', CurrencySelect);
```

