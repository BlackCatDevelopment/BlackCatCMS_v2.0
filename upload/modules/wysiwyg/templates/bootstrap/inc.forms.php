<?php
$FORMS = array(
    'options' => array(
        array(
            'type' => 'hidden',
            'name' => 'section_id',
        ),
        array(
            'type'     => 'checkbox',
            'label'    => 'Use border',
            'name'     => 'border',
            'options'  => array(1=>'Yes'),
            'value'    => 1,
            'helptext' => 'Adds CSS class "border" to the columns',
        ),
        array(
            'type'     => 'radio',
            'label'    => 'Columns per row',
            'name'     => 'columns',
            'options'  => array_merge(array(0=>'auto'),range(1,6)),
            'value'    => 3,
        ),
        array(
            'type'     => 'text',
            'label'    => 'Optional overall heading',
            'name'     => 'heading_text',
            'helptext' => 'Places a heading on top of the content. Useful for f.e. multi-column content, Accordions, Tabs etc.',
        ),
        array(
            'type'     => 'radio',
            'label'    => 'Heading level',
            'name'     => 'heading_level',
            'options'  => array(1=>1,2,3,4,5,6),
            'value'    => 2,
            'helptext' => '&lt;h1&gt; - &lt;h6&gt; - Note: Remember SEO rules. Level-1-Headings should be on top of the page.',
        ),
        array(
            'type'     => 'textarea',
            'label'    => 'Optional overall text',
            'name'     => 'overall_text',
            'helptext' => 'Places text between optional overall heading and section content. Hint: Keep it short.',
        ),
        array(
            'type'     => 'button',
            'name'     => 'submit',
            'name'     => 'submit',
            'label'    => 'Submit',
            'class'    => 'btn btn-primary',
        ),
        array(
            'type'     => 'button',
            'name'     => 'cancel',
            'label'    => 'Cancel & close',
            'class'    => 'btn btn-secondary',
        ),
    )
);