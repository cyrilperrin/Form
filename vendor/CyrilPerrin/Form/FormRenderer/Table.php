<?php

namespace CyrilPerrin\Form;

/**
 * Form renderer with table
 */
class FormRenderer_Table extends FormRenderer_Abstract
{

    /**
     * @see FormRenderer_Abstract#renderElement(Form,Field)
     */
    protected function renderElement(Form $form,Field $element)
    {
        $string = '<tr>';
        if ($element->getDescription() != null) {
            $string .= '<td style="vertical-align:top;">'.
                       $element->getDescription().' :</td><td>';
        } else {
            $string .= '<td colspan="2">';
        }
        $string .= $element->__toString().' '.$form->getError($element).'</td>'.
                   '</tr>'."\n";
        return $string;
    }

    /**
     * @see FormRenderer_Abstract#getListStart()
     */
    protected function getListStart()
    {
        return '<table>'."\n";
    }

    /**
     * @see FormRenderer_Abstract#getListEnd()
     */
    protected function getListEnd()
    {
        return '</table>'."\n";
    }

}
