<?php
use SilverStripe\Admin\CMSMenu;
use WebbuildersGroup\AddToCampaigns\Control\Admin\AddToCampaignController;

CMSMenu::remove_menu_item(str_replace('\\', '-', AddToCampaignController::class));
