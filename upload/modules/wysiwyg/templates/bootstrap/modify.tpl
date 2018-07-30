{if $options.columns}{$cols=$options.columns}{else}{$cols=count($columns)}{$cols=$cols-1}{/if}
<div class="container-flex">
    <div class="row">
    {for i 0 $cols}
        <div class="col mx-1">
            <div id="editor_{$section_id}_{$i}" contenteditable="true">
            {$columns.$i}
            </div>
        </div>
    {/for}
    </div>
</div>

<!--
section_id = '48'
action = '//localhost:444/site1/backend/section/save/48'
width = '100%'
height = '350px'
columns (array):
0 = '<h2>Enjoy the difference!</h2> <p>BlackCat CMS bietet das perfekte&nbsp;System für fast jeden Einsatzbereich. Modern, intuitiv, leicht erweiterbar und dabei kinderleicht zu installieren.&nbsp;Überzeugen Sie sich selbst von den Vorteilen und lassen Sie sich begeistern!</p> '
1 = '<p>Lorem ipsum dolor sit amet consectetuer sapien laoreet elit ipsum porttitor. Odio sed Curabitur semper odio tincidunt felis ut lobortis Morbi eu. Pellentesque sit mollis justo sem Vestibulum rutrum pellentesque Ut ut id. Et tincidunt adipiscing netus nunc augue lorem tempus interdum mollis orci. Consequat tellus condimentum eu pede ut.</p> <p>Id eget laoreet sed augue natoque sollicitudin lobortis ut Lorem Integer. Et vel eget a Quisque platea ac malesuada lobortis et tristique. Nulla at libero laoreet congue leo nisl vitae quis iaculis justo. Ut auctor quis augue tincidunt enim quis In interdum dui mus. Pellentesque pellentesque leo et at Phasellus diam morbi semper rhoncus tempus. </p> <p>Semper felis risus semper urna justo nunc laoreet malesuada convallis leo. Orci ut Praesent Nullam Vestibulum laoreet Aenean laoreet pede In et. Malesuada consectetuer Phasellus Curabitur Vivamus velit et sit nunc elit et. Metus Nam ipsum vitae pellentesque id wisi vel sem sed sem. Consectetuer sed adipiscing Quisque massa id Phasellus tempus commodo et dui. Convallis parturient Maecenas condimentum eros nulla.</p> <p>Ante auctor nunc lacinia libero nulla velit ipsum vitae sollicitudin elit. Elit vestibulum sapien leo felis congue Aenean Lorem auctor nibh Donec. Rutrum wisi rutrum enim ut id tortor eros gravida consequat dolor. Felis lacus elit Pellentesque tortor congue ut metus enim nibh amet. Non pellentesque ante semper Vivamus ipsum Vestibulum leo Vestibulum metus orci. Orci tempor mi sodales.</p> '
options (array):
border = '1'
heading_level = '3'
heading_text = 'Overall heading'

{if $options.heading || $options.heading_text}
    <div class="row">
        <div class="col-md-12 heading">
            {if $options.heading_text}<h{if $options.heading_level}{$options.heading_level}{else}3{/if} class="title">{$options.heading_text}<span></span></h{if $options.heading_level}{$options.heading_level}{else}3{/if}>{/if}
            {if $options.overall_text}<p class="subtitle">{$options.overall_text}</p>{/if}
        </div>
    </div>
    {/if}
    {if $columns}{$cols = count($columns)}{/if}
    <div class="row">
    {for i 0 $cols}
        <div class="col{if $options.border} mx-1 border{/if}">{$content.$i}</div>
    {/for}
    </div>

-->