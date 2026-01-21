/**
 * FormValidation Utility
 * Handles standard HTML5 validation with Bootstrap styling on blur and submit.
 */
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) {
            console.warn(`FormValidator: Form with ID "${formId}" not found.`);
            return;
        }
        this.inputs = this.form.querySelectorAll('input, select, textarea');
        this.init();
    }

    init() {
        this.inputs.forEach(input => {
            // Validate on blur
            input.addEventListener('blur', () => this.validateField(input));

            // Clear validation on input if it was invalid
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    this.validateField(input);
                }
            });
        });
    }

    /**
     * Validates a single field and updates UI
     * @param {HTMLElement} field 
     * @returns {boolean} isValid
     */
    validateField(field) {
        // Skip hidden fields or those disabled
        if (field.type === 'hidden' || field.disabled) return true;

        let isValid = field.checkValidity();
        let feedback = field.parentElement.querySelector('.invalid-feedback');

        // If no feedback element exists, try to find it or creating it might be out of scope for strict simple validation
        // But let's assume standard bootstrap structure or add one if missing? 
        // For now, relies on existing HTML structure or generic message.

        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');

            if (feedback) {
                feedback.textContent = field.validationMessage;
            } else {
                // If the feedback div is missing, create it dynamically
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = field.validationMessage;
                field.parentElement.appendChild(feedback);
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }

        return isValid;
    }

    /**
     * Validates all fields in the form
     * @returns {boolean} true if all valid
     */
    validateAll() {
        let isFormValid = true;
        this.inputs.forEach(input => {
            if (!this.validateField(input)) {
                isFormValid = false;
            }
        });

        // Focus the first invalid input
        if (!isFormValid) {
            const firstInvalid = this.form.querySelector('.is-invalid');
            if (firstInvalid) firstInvalid.focus();
        }

        return isFormValid;
    }

    /**
     * Removes all validation classes
     */
    resetValidation() {
        this.inputs.forEach(input => {
            input.classList.remove('is-invalid');
            input.classList.remove('is-valid');
        });
    }
}
