<?php

namespace CyrilPerrin\Form;

/**
 * Class to extend to be considered as a form renderer
 */
abstract class FormRenderer
{
    /**
     * Render a form in HTML
     * @param $form Form form
     * @return string form in HTML
     */
    public function renderForm(Form $form)
    {
        // Form start
        $string = $form->getStart()."\n";

        // Display form error
        if (($error = $form->getError($form)) != null) {
            $string .= $error."\n";
        }

        // Display hidden fields
        foreach ($form->getFields() as $field) {
            if ($field instanceof Field_Input_Hidden) {
                $string .= $field->__toString();
            }
        }

        // Group fields
        $groups = array();
        foreach ($form->getFields() as $field) {
            // Add field to group
            if (!($field instanceof Field_Input_Hidden) &&
                !($field instanceof Field_Sequence) &&
                !($field instanceof Field_Submit) &&
                ($names = $form->getGroup($field)) !== null) {
                $group =& $groups;
                while (count($names)) {
                    $name = array_shift($names);
                    if (!isset($group[$name])) {
                        $group[$name] = array();
                    }
                    $group =& $group[$name];
                }
                $group[] = $field;
            }
        }

        // List started ?
        $startedList = false;

        // Display fields
        $done = array();
        foreach ($form->getFields() as $field) {
            if (!($field instanceof Field_Input_Hidden) &&
                !($field instanceof Field_Sequence) &&
                !($field instanceof Field_Submit)) {
                // Display field or group
                if (($names = $form->getGroup($field)) === null) {
                    // Start list
                    if (!$startedList) {
                        $string .= $this->getListStart();
                        $startedList = true;
                    }
                        
                    // Display field
                    $string .= $this->renderField($form, $field);
                } else {
                    // Get group name
                    $name = array_shift($names);
                        
                    if (!in_array($name, $done)) {
                        // Close list
                        if ($startedList) {
                            $string .= $this->getListEnd();
                            $startedList = false;
                        }

                        // Display group
                        $string .= $this->renderGroup(
                            $form, $name, $groups[$name]
                        );
                         
                        // Group is done
                        $done[] = $name;
                    }
                }
            }
        }
        if ($startedList) {
            $string .= $this->getListEnd();
        }

        // Display submit fields
        foreach ($form->getFields() as $field) {
            if ($field instanceof Field_Submit) {
                $string .= $field->__toString();
            }
        }

        // Form end
        $string .= $form->getEnd()."\n";

        // Return string
        return $string;
    }

    /**
     * Render a group in HTML
     * @param $form Form form
     * @param $name string group name
     * @param $fields array goup fields
     * @return string group in HTML
     */
    private function renderGroup($form,$name,$fields)
    {
        // Init string
        $string = '';

        // Fieldset
        if (is_string($name)) {
            $string .= '<fieldset>'."\n".'<legend>'.$name.'</legend>'."\n";
        }

        // List started ?
        $startedList = false;
         
        // Display group fields
        foreach ($fields as $key => $field) {
            // Display field or group
            if (is_array($field)) {
                // Close list
                if ($startedList) {
                    $string .= $this->getListEnd();
                    $startedList = false;
                }

                // Display group
                $string .= $this->renderGroup($form, $key, $field);
            } else {
                // Start list
                if (!$startedList) {
                    $string .= $this->getListStart();
                    $startedList = true;
                }

                // Display field
                $string .= $this->renderField(
                    $form, $field, is_string($name)
                );
            }
        }
        if ($startedList) {
            $string .= $this->getListEnd();
        }

        // Fieldset
        if (is_string($name)) {
            $string .= '</fieldset>'."\n";
        }

        // Return string
        return $string;
    }

    /**
     * Render a field in HTML
     * @param $form Form form
     * @param $field Field field
     * @return string field in HTML
     */
    abstract protected function renderField(Form $form,Field $field);

    /**
     * Get list start
     * @return string list start
    */
    abstract protected function getListStart();

    /**
     * Get list end
     * @return string list end
    */
    abstract protected function getListEnd();
}
