<?php

namespace CyrilPerrin\Form;

/**
 * Abstract form renderer
 */
abstract class FormRenderer_Abstract implements IFormRenderer
{
    /**
     * @see IFormRenderer#renderForm(Form)
     */
    public function renderForm(Form $form)
    {
        // Form start
        $string = $form->getStart()."\n";

        // Display form error
        if (($error = $form->getError($form)) != null) {
            $string .= $error."\n";
        }

        // Display hidden elements
        foreach ($form->getElements() as $element) {
            if ($element instanceof Field_Input_Hidden) {
                $string .= $element->__toString();
            }
        }

        // Group elements
        $groups = array();
        foreach ($form->getElements() as $element) {
            // Add element to group
            if (!($element instanceof Field_Input_Hidden) &&
                !($element instanceof Field_Sequence) &&
                !($element instanceof Field_Submit) &&
                ($names = $form->getGroup($element)) !== null) {
                $group =& $groups;
                while (count($names)) {
                    $name = array_shift($names);
                    if (!isset($group[$name])) {
                        $group[$name] = array();
                    }
                    $group =& $group[$name];
                }
                $group[] = $element;
            }
        }

        // List started ?
        $startedList = false;

        // Display elements
        $done = array();
        foreach ($form->getElements() as $element) {
            if (!($element instanceof Field_Input_Hidden) &&
                !($element instanceof Field_Sequence) &&
                !($element instanceof Field_Submit)) {
                // Display element or group
                if (($names = $form->getGroup($element)) === null) {
                    // Start list
                    if (!$startedList) {
                        $string .= $this->getListStart();
                        $startedList = true;
                    }
                        
                    // Display element
                    $string .= $this->renderElement($form, $element);
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

        // Display submit elements
        foreach ($form->getElements() as $element) {
            if ($element instanceof Field_Submit) {
                $string .= $element->__toString();
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
     * @param $elements array goup elements
     * @return string group in HTML
     */
    private function renderGroup($form,$name,$elements)
    {
        // Init string
        $string = '';

        // Fieldset
        if (is_string($name)) {
            $string .= '<fieldset>'."\n".'<legend>'.$name.'</legend>'."\n";
        }

        // List started ?
        $startedList = false;
         
        // Display group elements
        foreach ($elements as $key => $element) {
            // Display element or group
            if (is_array($element)) {
                // Close list
                if ($startedList) {
                    $string .= $this->getListEnd();
                    $startedList = false;
                }

                // Display group
                $string .= $this->renderGroup($form, $key, $element);
            } else {
                // Start list
                if (!$startedList) {
                    $string .= $this->getListStart();
                    $startedList = true;
                }

                // Display element
                $string .= $this->renderElement(
                    $form, $element, is_string($name)
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
     * Render an element in HTML
     * @param $form Form form
     * @param $element Field element
     * @return string element in HTML
     */
    abstract protected function renderElement(Form $form,Field $element);

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
