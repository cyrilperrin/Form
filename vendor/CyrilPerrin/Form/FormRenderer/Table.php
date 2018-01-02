<?php

namespace CyrilPerrin\Form\FormRenderer;

use CyrilPerrin\Form\Field;
use CyrilPerrin\Form\Form;
use CyrilPerrin\Form\FormRenderer;

/**
 * Form renderer with table
 */
class Table extends FormRenderer
{

    /**
     * @see FormRenderer#renderField(Form,Field)
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
     * @see FormRenderer#getListStart()
     */
    protected function getListStart()
    {
        return '<table>'."\n";
    }

    /**
     * @see FormRenderer#getListEnd()
     */
    protected function getListEnd()
    {
        return '</table>'."\n";
    }

}
