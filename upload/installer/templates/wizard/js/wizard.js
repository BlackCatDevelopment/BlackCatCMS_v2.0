$(document).ready(function() {

    function poll() {
    }

    $(document).on('invalid.bs.validator',function(e) {
        console.log('invalid',e.detail);
    });

    // add progress indicator and step numbers
    var n=1;
    $('ul#wizHeader').addClass('progress-indicator');
    $('ul#wizHeader li a').each(function() {
        $(this).before('<span class="bubble">'+n+'</span>');
        n++;
    })
    var laststep = (n-1);

    // add Bootstrap Wizard
    $('#rootwizard').bootstrapWizard({
        tabClass: '',
        onPrevious: function(tab, navigation, index) {
            $('ul#wizHeader').find('a[href="#step-'+(index+1)+'"]').parent().removeClass('completed');
        },
        onNext: function(tab, navigation, index) {
            var   curStep = $('#step-'+index),
                curInputs = curStep.find("input"),
                  isValid = true;
            for(var i=0; i<curInputs.length; i++) {
                if (!curInputs[i].validity.valid) {
                    isValid = false;
                    $(curInputs[i]).closest(".form-group").addClass("has-error");
                }
            }
            if(!isValid) {
                return false; // disable step change
            } else {
                $('ul#wizHeader').find('a[href="#step-'+index+'"]').parent().addClass('completed');
                $.ajax({
                    type: "POST",
                    url: CAT_INST_URL + "/save.php",
                    data: $("form").serialize(),
                    error: function(response)   { console.log('!!!ERROR!!!',response); },
                    success: function(response) { console.log('<<<SUCCESS>>>',response); }
                });
            }
        },
        onTabShow: function(tab, navigation, index) {
            var total = navigation.find('li').length;
            if(index==0) {
                $('.previous').hide();
            } else {
                $('.previous').show();
            }
            $('.install').hide();
            if(index==(total-2)) {
                $('.next').hide();
                $('.install').show();
            } else {
                $('.next').show();
            }
			var current = index+1;
			var percent = (current/total) * 100;
			$('#rootwizard .progress-bar').css({width:percent+'%'});
		}
    });

    // disable installation step
    $('#rootwizard').find("a[href*='step-"+laststep+"']").parent().addClass('disabled');

    // install button click
    $('li.install a').on('click', function(e) {
        $('#rootwizard').find("a[href*='step-"+laststep+"']").parent().removeClass('disabled');
        $('#rootwizard').find("a[href*='step-"+laststep+"']").trigger('click');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: CAT_INST_URL + "/install.php",
            data: { DO: true },
            error: function(response)   {
                console.log(response);
                $('div#bar').after('<div class="alert alert-danger">');
                $('div#bar').next('div').html('<span class="fa fa-warning"></span> Unable to start installation.');
            },
            success: function(response) {
                console.log(response);
                if(response.success !== true) {
                    $('#rootwizard').find("a[href*='step-"+(laststep-1)+"']").trigger('click');
                    $('div#bar').after('<div class="alert alert-danger">');
                    $('div#bar').next('div').html('<span class="fa fa-warning"></span> '+response.message);
                }
            }
        });

    });

    // bind list item click to tab change
    $('ul#wizHeader li').unbind('click').on('click', function(e) {
        var index = $(this).find('a').data('step');
        index = index - 1;
console.log('showing page: ', index);
        $('#rootwizard').bootstrapWizard('show',index);
    });

});