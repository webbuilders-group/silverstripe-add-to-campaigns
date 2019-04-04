<?php
namespace WebbuildersGroup\AddToCampaigns\Extensions;

use SilverStripe\CampaignAdmin\AddToCampaignHandler_FormAction;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use WebbuildersGroup\AddToCampaigns\Control\Admin\AddToCampaignController;


/**
 * Class \WebbuildersGroup\AddToCampaigns\Extensions\CampaignableGridField
 *
 * @property \SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest|\WebbuildersGroup\AddToCampaigns\Extensions\AddToCampaignButton $owner
 */
class CampaignableGridField extends Extension
{
    /**
     * Updates the grid field item request when applicable to support adding to campaigns
     * @param Form $form 
     */
    public function updateItemEditForm(Form $form)
    {
        $record = $this->owner->getRecord();
        $recordClass=$record->baseClass();
        if($record->has_extension(Versioned::class) && in_array($recordClass, AddToCampaignController::config()->campaignable_classes))
        {
            $form->addExtraClass('add-to-campaign-supported')
                ->setAttribute('data-addtocampaign-record-id', $this->owner->getRecord()->ID)
                ->setAttribute('data-addtocampaign-record-class', str_replace('\\', '-', $recordClass));
            
            
            // If the addtocampaign action has not been already added to the form add the button
            if (!$form->Actions()->fieldByName('action_addtocampaign'))
            {
                $isOnDraft = $record->isOnDraft();
                $isPublished = $record->isPublished();
                $canPublish = $record->canPublish();
                
                // Add to campaign option if not-archived and has publish permission
                if (($isPublished || $isOnDraft) && $canPublish)
                {
                    if (($parent = $form->Actions()->fieldByName('ActionMenus.MoreOptions')) == null)
                    {
                        $parent = $form->Actions();
                    }
                    
                    $parent->push(AddToCampaignHandler_FormAction::create()
                        ->removeExtraClass('btn-primary')
                        ->addExtraClass('btn-secondary')
                        ->setForm($form));
                }
            }
            
            Requirements::javascript('webbuilders-group/silverstripe-add-to-campaigns: javascript/AddToCampaigns.js');
        }
    }
}
