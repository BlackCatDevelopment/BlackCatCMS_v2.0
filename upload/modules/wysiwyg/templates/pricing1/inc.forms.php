<?php
$FORMS = array(
    'options' => array(
        array(
            'type'     => 'hidden',
            'name'     => 'section_id',
        ),
        // list of options to save
        array(
            'type'     => 'hidden',
            'name'     => 'options',
            'value'    => 'columns,lift_panel,lift_panel_text,currency,color_scheme',
        ),
        array(
            'type'     => 'radio',
            'label'    => 'Panels',
            'name'     => 'columns',
            'options'  => range(1,5),
            'value'    => 3,
            'helptext' => 'The number of panels to show; odd number looks best in most cases',
        ),
        array(
            'type'     => 'radio',
            'label'    => 'Lift panel',
            'name'     => 'lift_panel',
            'options'  => range(1,5),
            'value'    => 2,
            'helptext' => 'Lifts (elevates) the panel with the given column number; for &quot;best price&quot; or &quot;most popular&quot; markup',
        ),
        array(
            'type'     => 'text',
            'label'    => 'Lift panel text',
            'name'     => 'lift_panel_text',
            'value'    => 'Most popular',
            'helptext' => 'Added as additional heading to the lifted panel',
        ),
        array(
            'type'     => 'text',
            'label'    => 'Currency',
            'name'     => 'currency',
            'value'    => 'EUR',
            'helptext' => 'Currency; abbreviation (example: EUR, USD) or symbol (example: &amp;euro; for &euro;) work best',
        ),
        array(
            'type'     => 'select',
            'label'    => 'Color scheme',
            'name'     => 'color_scheme',
            'options'  => array('default'=>'default','dark'=>'dark','orange'=>'orange'),
            'value'    => 'default',
            'helptext' => 'The color schemes are defined in the frontend.css. This selection is defined in inc.forms.php.',
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