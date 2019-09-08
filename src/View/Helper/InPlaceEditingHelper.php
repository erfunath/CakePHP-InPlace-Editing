<?php

namespace InPlaceEditing\View\Helper;

use Cake\View\Helper;
use Cake\Log\Log;
use Cake\Error\Debugger;

/*
 * ----------------------------------------------------------------------------
 * Package:     CakePHP InPlaceEditing Plugin
 * Version:     0.0.1
 * Date:        2012-12-31
 * Description: CakePHP plugin for in-place-editing functionality of any
 *				form element.
 * Author:      Karey H. Powell
 * Author URL:  http://kareypowell.com/
 * Repository:  http://github.com/kareypowell/CakePHP-InPlace-Editing
 * ----------------------------------------------------------------------------
 * Copyright (c) 2012 Karey H. Powell
 * Dual licensed under the MIT and GPL licenses.
 * ----------------------------------------------------------------------------
 */

class InPlaceEditingHelper extends Helper
{

    public $helpers = ['Html', 'Url'];

    /*
     * Returns a script which contains a html element (type defined in a parameter) with the field contents.
     * And includes a script required for the inplace update ajax request logic.
     */
    public function input($modelName, $fieldName, $id, $settings = null)
    {
        $value         = $this::extractSetting($settings, 'value', '');
        $actionName    = $this::extractSetting($settings, 'actionName', 'inPlaceEditing');
        $type          = $this::extractSetting($settings, 'type', 'text');
        $cancelText    = $this::extractSetting($settings, 'cancelText', '');
        $submitText    = $this::extractSetting($settings, 'submitText', '');
        $toolTip       = $this::extractSetting($settings, 'toolTip', 'Click to edit.');
        $containerType = $this::extractSetting($settings, 'containerType', 'div');
        $csrfToken     = $this::extractSetting($settings, 'csrfToken', '');
        $rows          = $this::extractSetting($settings, 'rows', '1');
        $data          = $this::extractSetting($settings, 'data', '');
        $updateMaxPoints = $this::extractSetting($settings, 'updateMaxPoints', 'false');
        $updateID      = $this::extractSetting($settings, 'updateID', '');
        
        //remove quotation marks
        $csrfToken = trim($csrfToken, '"');
        $complete = '';
        $base_route = '';
        
        $route = $this->Url->build($actionName, true);

        if ($updateMaxPoints == 'true' && $updateID != '') {
            $base_route = $this->Url->build(['controller' => 'Rubrics', 'action' => 'getMaxPoints'], true);
            $complete = "complete: function() {
                $.ajax({
                    type: 'GET',
                    url: '$base_route/$updateID',
                    dataType: 'html',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-CSRF-Token',
                            '<?php echo $csrfToken ?>');
                    },
                    success: function(response) {
                        $('#rubric-$updateID-max-points').html(response);
                        
                    },
                });
            },";
        }

        $elementID = 'inplace_'.$modelName.'_'.$fieldName.'_'.$id;
        $input     = '<'.$containerType.' id="'.$elementID.'" class="in_place_editing">'.$value.'</'.$containerType.'>';        
        $script    = "$(function(){
                        $('#$elementID').editable('$route/$id', {
                                name      : '$fieldName',
                                type      : '$type',
                                cancel    : '$cancelText',
                                submit    : '$submitText',
                                tooltip   : '$toolTip',
                                rows      : '$rows',
                                data      : '$data',
                                cssclass  : 'ui-widget',
                                submitcssclass: 'ui-button ui-widget ui-corner-all',
                                cancelcssclass: 'ui-button ui-widget ui-corner-all',
                                indicator : '<div class=\'ajaxsaving\'></div>',
                                ajaxoptions : {
                                    beforeSend: function(xhr){
                                        xhr.setRequestHeader('X-CSRF-Token', '$csrfToken');
                                    },
                                $complete
                                }
                            }
                        );
                    });";
        $this->Html->scriptBlock($script, ['block' => 'scriptBottom']);
        return $input;
    }

    /*
     * Extracts a setting under the provided key if possible, otherwise, returns a provided default value.
     */
    protected static function extractSetting($settings, $key, $defaultValue = '')
    {
        if ( ! $settings && empty($settings)) {
            return $defaultValue;
        }

        if (isset($settings[$key])) {
            return $settings[$key];
        } else {
            return $defaultValue;
        }
    }

}
