### Naming
Story branch should be created for each repository, which requires any code-changes by the following pattern: 

**"story-\<id\>\[-\<tags\>\]"**, e.g. "story-25-sync-categories" or just "story-25"

Story sub-task may have separate PR which should be named according to the pattern

**"story-\<id\>-task-\<task id\>[-\<tags\>\]"**, e.g. "story-25-task-67-category-feed" or just "story-25-task-67

### Delivery

- sub-task can have its own PR and can be reviewed independently
- sub-task PR should be merged only to story branch
- only story branch can be merged to mainline

If several developers work on the same story, please **do not** commit directly to the story branch, but merge changes from sub-task branch.

See also:
https://github.com/magento/catalog-storefront/wiki/Temporary-strategy-for-mergeing-PR-to-mainline-branches