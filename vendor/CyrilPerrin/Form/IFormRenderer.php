<?php

namespace CyrilPerrin\Form;

/**
 * Interface to implement to be considered as a form renderer
 */
interface IFormRenderer
{

    /**
     * Render a form in HTML
     * @param $form Form form
     * @return string form in HTML
     */
    public function renderForm(Form $form);

}
