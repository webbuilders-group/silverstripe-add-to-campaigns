/* global i18n, jQuery */
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector';

const FormBuilderModal = loadComponent('FormBuilderModal');

jQuery.entwine('ss', ($) => {
    $('.add-to-campaign-supported .cms-content-actions #Form_ItemEditForm_action_addtocampaign').entwine({
        onclick() {
            let dialog = $('#add-to-campaign__dialog-wrapper.addtocampaigns-controlled');
            
            if (!dialog.length) {
                dialog = $('<div id="add-to-campaign__dialog-wrapper" class="addtocampaigns-controlled" />');
                $('body').append(dialog);
            }
            
            dialog.open();
            
            return false;
        }
    });
    
    $('#add-to-campaign__dialog-wrapper.addtocampaigns-controlled').entwine({
        _renderModal(isOpen) {
            const handleHide = () => this.close();
            const handleSubmit = (...args) => this._handleSubmitModal(...args);
            const form = $('form.add-to-campaign-supported');
            const id = form.attr('data-addtocampaign-record-id');
            const recordClass = form.attr('data-addtocampaign-record-class');
            const store = window.ss.store;
            const sectionConfig = store.getState().config.sections.find((section) => section.name === 'WebbuildersGroup\\AddToCampaigns\\Control\\Admin\\AddToCampaignController');
            const schemaUrl = sectionConfig.form.AddToCampaignForm.schemaUrl;
            const modalSchemaUrl = `${schemaUrl}/${recordClass}/${id}`;
            const title = i18n._t('Admin.ADD_TO_CAMPAIGN', 'Add to campaign');

            ReactDOM.render(
                <FormBuilderModal
                    title={title}
                    isOpen={isOpen}
                    onSubmit={handleSubmit}
                    onClosed={handleHide}
                    schemaUrl={modalSchemaUrl}
                    bodyClassName="modal__dialog"
                    className="add-to-campaign-modal"
                    responseClassBad="modal__response modal__response--error"
                    responseClassGood="modal__response modal__response--good"
                    identifier="Admin.AddToCampaign"
                />,
                this[0]
            );
        }
    });
});