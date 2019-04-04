<?php
namespace WebbuildersGroup\AddToCampaigns\Control\Admin;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\CampaignAdmin\AddToCampaignHandler;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Versioned\Versioned;


class AddToCampaignController extends LeftAndMain
{
    private static $url_segment = 'add-to-campaign';
    
    private static $url_handlers = [
        'GET schema/$FormName/$ModelClass/$ItemID/$OtherItemID' => 'schema',
        'GET methodSchema/$Method/$FormName/$ItemID' => 'methodSchema',
    ];
    
    private static $allowed_actions = [
        'AddToCampaignForm',
    ];
    
    /**
     * Classes allowed to be added to campaigns though this controller
     * @config WebbuildersGroup\AddToCampaigns\Control\Admin\AddToCampaignController.campaignable_classes
     * @var array
     */
    private static $campaignable_classes = [];
    
    /**
     * 404 on index requests, we don't want users accidentally stumbling into here
     */
    public function index($request)
    {
        return $this->httpError(404);
    }
    
    /**
     * Returns configuration required by the client app.
     *
     * @return array
     */
    public function getClientConfig()
    {
        $config = parent::getClientConfig();
        
        $config['form']['AddToCampaignForm'] = [
            'schemaUrl' => $this->Link('schema/AddToCampaignForm')
        ];
        
        return $config;
    }
    
    /**
     * Action handler for adding pages to a campaign
     *
     * @param array $data
     * @param Form $form
     * @return DBHTMLText|HTTPResponse
     */
    public function addtocampaign($data, $form)
    {
        $id = $data['ID'];
        $modelClass = $this->unsanitiseClassName(($this->request->param('ModelClass') ?: $this->request->postVar('ClassName')));
        
        // Ensure we have the base data class
        if(!empty($modelClass) && !class_exists($modelClass))
        {
            $modelClass = DataObjectSchema::singleton()->baseDataClass($modelClass);
        }
        
        // Make sure the class exists and is allowed
        if(empty($modelClass) || !class_exists($modelClass) || !in_array($modelClass, $this->config()->campaignable_classes) || !$modelClass::has_extension(Versioned::class))
        {
            $this->httpError(404, _t(__CLASS__ . '.ErrorNotFound', 'That {Type} couldn\'t be found', '', array(
                'Type' => $modelClass
            )));
            
            return null;
        }
        
        $record = $modelClass::get()->byID($id);
        $handler = AddToCampaignHandler::create($this, $record);
        $results = $handler->addToCampaign($record, $data);
        
        if (is_null($results)) {
            return null;
        }
        
        if ($this->getSchemaRequested()) {
            // Send extra "message" data with schema response
            $extraData = array(
                'message' => $results
            );
            $schemaId = Controller::join_links($this->Link('schema/AddToCampaignForm'), $modelClass, $id);
            
            return $this->getSchemaResponse($schemaId, $form, null, $extraData);
        }
        
        return $results;
    }
    
    /**
     * Url handler for add to campaign form
     *
     * @param HTTPRequest $request
     * @return Form
     */
    public function AddToCampaignForm($request)
    {
        // Get ID either from posted back value, or url parameter
        $id = ($request->param('OtherID') ?: $request->postVar('ID'));
        $class = $this->unsanitiseClassName(($request->param('ModelClass') ?: $request->postVar('ClassName')));
        
        // Ensure we have the base data class
        if(!empty($class) && !class_exists($class))
        {
            $class = DataObjectSchema::singleton()->baseDataClass($class);
        }
        
        return $this->getAddToCampaignForm($id, $class);
    }
    
    /**
     * Gets the form used for adding a record to a campaign
     *
     * @param int $id
     * @return Form
     */
    public function getAddToCampaignForm($id, $modelClass=null)
    {
        if(empty($modelClass))
        {
            $modelClass = $this->unsanitiseClassName(($this->request->param('ModelClass') ?: $this->request->postVar('ClassName')));
            
            // Ensure we have the base data class
            if(!empty($modelClass) && !class_exists($modelClass))
            {
                $modelClass = DataObjectSchema::singleton()->baseDataClass($modelClass);
            }
        }
        
        // Make sure the class exists and is allowed
        if (empty($modelClass) || !class_exists($modelClass) || !in_array($modelClass, $this->config()->campaignable_classes) || !$modelClass::has_extension(Versioned::class))
        {
            $this->httpError(404, _t(__CLASS__ . '.ErrorNotFound', 'That {Type} couldn\'t be found', '', array(
                'Type' => $modelClass
            )));
            
            return null;
        }
        
        // Get record-specific fields
        $record = $modelClass::get()->byID($id);
        
        if (!$record) {
            $this->httpError(404, _t(__CLASS__ . '.ErrorNotFound', 'That {Type} couldn\'t be found', '', array(
                'Type' => $modelClass::singleton()->i18n_singular_name()
            )));
            
            return null;
        }
        
        if (!$record->canView()) {
            $this->httpError(403, _t(__CLASS__ . '.ErrorItemPermissionDenied', 'It seems you don\'t have the necessary permissions to add {ObjectTitle} to a campaign', '', array(
                'ObjectTitle' => $modelClass::singleton()->i18n_singular_name()
            )));
            
            return null;
        }
        
        $handler = AddToCampaignHandler::create($this, $record);
        $form = $handler->Form($record);
        
        $form->setValidationResponseCallback(function (ValidationResult $errors) use ($form, $modelClass, $id) {
            $schemaId = Controller::join_links($this->Link('schema/AddToCampaignForm'), $this->sanitiseClassName($modelClass), $id);
            
            return $this->getSchemaResponse($schemaId, $form, $errors);
        });
        
        return $form;
    }
    
    /**
     * Sanitise a model class' name for inclusion in a link
     *
     * @param string $class
     * @return string
     */
    protected function sanitiseClassName($class)
    {
        return str_replace('\\', '-', $class);
    }
    
    /**
     * Unsanitise a model class' name from a URL param
     *
     * @param string $class
     * @return string
     */
    protected function unsanitiseClassName($class)
    {
        return str_replace('-', '\\', $class);
    }
}
