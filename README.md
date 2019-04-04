Add to Campaigns
=================
Capability to add any Versioned object to a campaign easily for CMS Admins via an "Add to Campaign" button in the CMS just like pages.

## Maintainer Contact
* Ed Chipman ([UndefinedOffset](https://github.com/UndefinedOffset))

## Requirements
* SilverStripe Campaign Admin 1.2+


## Installation
```
composer require webbuilders-group/silverstripe-add-to-campaigns
```


## Usage

By default all `SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest` instances will get an extension that will automatically configure the `GridFieldDetailForm_ItemRequest` edit form for the "Add to Campaign" functionality for all allowed classes. To add a class to the allowed support in your `config.yml` you must add the below. The `DataObject` must include the `SilverStripe\Versioned\Versioned` extension as well.

```yml
WebbuildersGroup\AddToCampaigns\Control\Admin\AddToCampaignController:
    campaignable_classes:
        - 'Full\DataObject\ClassName\Including\Namespace'
```


If you have not modified your `GridFieldDetailForm_ItemRequest` actions from the default `GridFieldDetailForm_ItemRequest::getFormActions()` the "Add to Campaign" button should be automatically be added to all allowed classes. If not you can add the following to were ever you are setting up your form actions for your `DataObject`.

```php
use SilverStripe\CampaignAdmin\AddToCampaignHandler_FormAction;

/** ... **/

if (($myDataObject->isPublished() || $myDataObject->isOnDraft()) && $myDataObject->canPublish()) {
    $moreOptions->push(AddToCampaignHandler_FormAction::create()
        ->removeExtraClass('btn-primary')
        ->addExtraClass('btn-secondary'));
}
```
