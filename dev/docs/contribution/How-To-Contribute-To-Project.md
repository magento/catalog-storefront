Two main points that is underlie the following instruction:
1) magento org is a read-only replica of new primary fork visible only to internal teams
2) sync from that invisible primary fork to magento org happens in eventually consistent way

The example would be given for complex task that has a code changes for catalog-storefront and commerce-data-export repos but whatever repo of Storefront project you need to contribute the flow will look same. 

## Step #1
Make a fork of catalog-storefront and commerce-data-export repos from magento org to your own organisation. 
## Step #2 [optional]
If you are working together with someone else in a team, please create your story-branch from magento/catalog-storefront/develop branch and push it to your fork.
For example, you need auto-generated files from proto-scheme, then someone from internal magento team would be able to create a PR to your story-branch.
## Step #3
When you ready, create a PR from your fork to magento/catalog-storefront and magento/commerce-data-export repos

*Note:* don't forget to link the PRs by providing this information under the _Related Pull Requests_ section
*Examples:*
```
### Related Pull Requests
https://github.com/magento/commerce-data-export/pull/9999999
<!-- related pull request placeholder -->
```
## Step #4
Run the tests on github by adding next comment to magento/catalog-storefront PR:
```
@magento run all tests
```

*Note:* if you need to run some specific kind of tests you can add next comment:
```
@magento run Integration Tests
```

*Exception:* if you have changes only to magento2 project for example and don't have any changes to catalog-storefront, that means you have nothing to contribute to catalog-storefront.
It such rarer case you have to create a dummy-PR to catalog-storefront with any changes, but, please put in the begining of Pr's title "[DUMMY-PR]". We would know that this shouldn't be merged.
## Step #5
When all the tests would be green, please ask someone from internal team to migrate the PR to magento-commerce.

*Note:* this is the comment that has to appear on all of your PRs you want contribute: 
```
@magento import pr to magento-team/catalog-storefront-ce
or
@magento import code to magento-team/catalog-storefront-ce
```
It has to be done for each PR.
If the gatekeeper miss any of the PRs, please notify him/her.
## Step #6
Once changes would be propagated to magento-commerce org they would be visible(eventually) in magento org.
And yes, only after that we will close your PRs with the nice comment that we appreciate your contribution and changes were merged to the main fork of our project.

*Note:* technically, your PRs would be closed and not merged in regard to magento org forks. That's because our magento/\* forks became a read-only from now. The main fork would be inside another org. You can reason about that as some abstract "Primary fork" and "magento/*" as a replica fork.
Eventually, your commits would be synced to magento/\* fork, and you would be the contributor of those changes, in that regards nothing changed. Only PRs would be displayed as 'Closed' instead of 'Merged'. 