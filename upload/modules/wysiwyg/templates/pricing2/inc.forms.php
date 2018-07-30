<?php
$FORMS = array(
    'options' => array(
        array(
            'type' => 'hidden',
            'name' => 'section_id',
        ),
        // list of options to save
        array(
            'type'     => 'hidden',
            'name'     => 'options',
            'value'    => 'columns,lift_panel,currency',
        ),
        array(
            'type'     => 'radio',
            'label'    => 'Panels',
            'name'     => 'columns',
            'options'  => range(1,5),
            'helptext' => 'The number of panels to show; odd number looks best in most cases',
        ),
        array(
            'type'     => 'radio',
            'label'    => 'Lift panel',
            'name'     => 'lift_panel',
            'options'  => range(1,5),
            'helptext' => 'Lifts (elevates) the panel with the given column number; for &quot;best price&quot; or &quot;most popular&quot; markup',
        ),
        array(
            'type'     => 'text',
            'label'    => 'Currency',
            'name'     => 'currency',
            'value'    => 'EUR',
            'helptext' => 'Currency; abbreviation (example: EUR, USD) or symbol (example: &amp;euro; for &euro;) work best',
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