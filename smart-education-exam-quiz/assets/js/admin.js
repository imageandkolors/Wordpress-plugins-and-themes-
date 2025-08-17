jQuery(document).ready(function($) {
    'use strict';

    // MCQ Options
    var optionsWrapper = $('#se-mcq-options-wrapper');
    var optionTemplate = wp.template('se-mcq-option');

    // Add option
    $('#se-add-option').on('click', function(e) {
        e.preventDefault();
        var index = optionsWrapper.find('.se-mcq-option').length;
        var newOption = optionTemplate({ index: index });
        optionsWrapper.append(newOption);
    });

    // Remove option
    optionsWrapper.on('click', '.se-remove-option', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to remove this option?')) {
            $(this).closest('.se-mcq-option').remove();
        }
    });
});
