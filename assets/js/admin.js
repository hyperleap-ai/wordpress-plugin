'use strict';

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize page selection functionality
    const locationSelect = document.getElementById('location');
    const pagesSelect = document.getElementById('pages');
    const pageSelection = document.querySelector('.page-selection');

    if (locationSelect) {
        locationSelect.addEventListener('change', function() {
            const isSpecific = this.value === 'specific';
            
            if (isSpecific) {
                pageSelection.style.display = 'block';
                if (!pagesSelect.value || pagesSelect.selectedOptions.length === 0) {
                    pagesSelect.required = true;
                }
            } else {
                pageSelection.style.display = 'none';
                pagesSelect.required = false;
            }
        });
    }

    // Form submission handler
    const chatbotForm = document.getElementById('chatbot-form');
    if (chatbotForm) {
        chatbotForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitButton = this.querySelector('#save-chatbot');
            submitButton.disabled = true;

            try {
                const formData = new FormData(this);
                formData.append('action', 'validate_chatbot'); // First check if chatbot exists
                formData.append('nonce', websiteChatbotsAdmin.nonce);

                // Check if chatbot ID already exists
                const validateResponse = await fetch(websiteChatbotsAdmin.ajaxurl, {
                    method: 'POST',
                    body: formData
                });
                const validateResult = await validateResponse?.json();
                if (!validateResult.success) {
                    throw new Error('A chatbot with this ID already exists. Edit the existing chatbot or use a different ID.');
                }

                // check if chatbot credentials are valid and exists or accessible
                const chatbotId = formData.get('chatbot_id');
                const privateKey = formData.get('private_key');
                const validationUrl = `https://api.hyperleapai.com/api/chatbots/public/${encodeURIComponent(chatbotId)}?key=${encodeURIComponent(privateKey)}`;
                const validationResponse = await fetch(validationUrl);
                const validationData = await validationResponse?.json(); 

                if (validationResponse.status !== 200) {
                    throw new Error('Something went wrong.',{cause: 'Invalid chatbot credentials. Please check your ID and key.'});
                }

                formData.append('chatbot_name', validationData.chatbotName);
                formData.set('action', 'save_chatbot'); 
                const saveResponse = await fetch(websiteChatbotsAdmin.ajaxurl, {
                    method: 'POST',
                    body: formData
                });

                const result = await saveResponse?.json();

                if (result.success) {
                    const redirectUrl = new URL(websiteChatbotsAdmin.listUrl);
                    window.location.href = redirectUrl.toString();
                } else {
                    throw new Error('Something went wrong.',{cause: 'Error saving chatbot'});
                }
            } catch (error) {
                const notice = document.createElement('div');
                notice.className = 'notice notice-error is-dismissible';
                notice.innerHTML = `
                    <p>Error: ${error.cause?? 'Invalid chatbot credentials. Please check your ID and key.'}</p>
                    <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>`;
                
                const heading = document.querySelector('h1');
                const existingNotices = heading.parentNode.querySelectorAll('.notice');
                existingNotices.forEach(notice => notice.remove());
                heading.parentNode.insertBefore(notice, heading.nextSibling);
                notice.querySelector('.notice-dismiss').addEventListener('click', () => {
                    notice.remove();
                });
                
                submitButton.disabled = false;
            }
        });
    }

    // Initialize Select2 if available
    if (typeof Select2 !== 'undefined' && pagesSelect) {
        new Select2(pagesSelect, {
            placeholder: 'Select pages',
            allowClear: true,
            width: '100%'
        });
    }
});