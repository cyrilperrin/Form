<?php

namespace CyrilPerrin\Form;

/**
 * Form renderer with table
 */
class FormRenderer_Table extends FormRenderer_Abstract
{

    /**
     * @see FormRenderer_Abstract#renderField(Form,Field)
     */
    protected function renderField(Form $form,Field $field)
    {
        $string = '<tr>';
        if ($field->getDescription() != null) {
            $string .= '<td style="vertical-align:top;">'.
                       $field->getDescription().' :</td><td>';
        } else {
            $string .= '<td colspan="2">';
        }
        $string .= $field->__toString().' '.$form->getError($field).'</td>'.
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
