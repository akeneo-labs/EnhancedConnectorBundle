define(
    ['jquery'],
    function ($) {
        'use strict';

        function initialize() {
            $('select.select2[multiple="multiple"]').each(function () {
                var $selectHtml = $(this);
                var $dropdown = $selectHtml.data('select2').dropdown;

                /**
                 * Adds the remove buttons to the groups
                 */
                $selectHtml.on('select2-loaded', function () {
                    //If a search is ongoing, there is no use for a remove button
                    if ($selectHtml.data('select2').search.val().length == 0) {
                        //We do not add the buttons on options that are not within an optgroup
                        var $options = $dropdown.find('.select2-results-dept-0.select2-result-with-children');
                        $options.children('.select2-result-label').each(function () {
                            $(this).append('<i class="select2-search-choice-close icon-remove"></i>');
                        });
                    }
                });

                /**
                 * Adds the clicked group to the selection
                 */
                $dropdown.on('click', '.select2-results-dept-0.select2-result-with-children>.select2-result-label', function () {
                    var selectedData = $selectHtml.select2('val');
                    var $elementsToAdd = $(this).parent().find('.select2-results-dept-1').not('.select2-selected');

                    $elementsToAdd.each(function () {
                        selectedData.push($(this).data('select2Data').id);
                    });

                    $selectHtml.select2('val', selectedData).trigger('change');
                });

                /**
                 * Removes the clicked group from the selection
                 */
                $dropdown.on('click', '.icon-remove', function (e) {
                    e.stopPropagation();
                    var selectedData = $selectHtml.select2('val');
                    var $elementsToRemove = $(this).parent().next().find('.select2-results-dept-1.select2-selected');

                    $elementsToRemove.each(function () {
                        var index = selectedData.indexOf($(this).data('select2Data').id);

                        if (index >= 0) {
                            selectedData.splice(index, 1);
                        }
                    });

                    $selectHtml.select2('val', selectedData).trigger('change');
                });
            });
        }

        return {
            initialize: initialize
        };
    }
);