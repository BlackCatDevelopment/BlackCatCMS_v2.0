$(function() {

alert('HUHU');

// ----- START http://bootsnipp.com/snippets/featured/form-wizard-and-validation -----
    var navListItems = $('div.setup-panel div a'),
            allWells = $('.setup-content'),
          allNextBtn = $('.nextBtn'),
          allPrevBtn = $('.prevBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
              $item = $(this);

        // added by shadowcat: missing data alert
        if($('tr.danger').length) {
            $('div#summary_missing_data').show();
        } else {
            $('div#summary_missing_data').hide();
        }
        // added by shadowcat: end

        if($('.has-error').length) {
        }

        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            // added by shadowcat: highlight current step
            $('div').removeClass('current');
            $item.parent().addClass('current');
            // added by shadowcat: end
            $item.addClass('btn-primary');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });

    allNextBtn.click(function(){
        // added by shadowcat: save step data
        $.ajax({
            type: "POST",
            url: CAT_INST_URL + "/save.php",
            data: $("form").serialize(),
            error: function(response)   { console.log('!!!ERROR!!!',response); },
            success: function(response) { console.log('<<<SUCCESS>>>',response); }
        });
        // added by shadowcat: end

        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='url']"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        // added by shadowcat: missing data alert
        if($('tr.danger').length) {
            $('div#summary_missing_data').show();
        } else {
            $('div#summary_missing_data').hide();
        }
        // added by shadowcat: end

        if (isValid)
            nextStepWizard.removeAttr('disabled').trigger('click');
    });

    allPrevBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            prevStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a");

        $(".form-group").removeClass("has-error");
        prevStepWizard.removeAttr('disabled').trigger('click');
    });

    $('div.setup-panel div a.btn-primary').trigger('click');

// ----- END http://bootsnipp.com/snippets/featured/form-wizard-and-validation -----

// ----- START http://bootsnipp.com/snippets/featured/password-strength-popover -----

    //minimum 8 characters
    var bad = /(?=.{8,}).*/;
    //Alpha Numeric plus minimum 8
    var good = /^(?=\S*?[a-z])(?=\S*?[0-9])\S{8,}$/;
    //Must contain at least one upper case letter, one lower case letter and (one number OR one special char).
    var better = /^(?=\S*?[A-Z])(?=\S*?[a-z])((?=\S*?[0-9])|(?=\S*?[^\w\*]))\S{8,}$/;
    //Must contain at least one upper case letter, one lower case letter and (one number AND one special char) plus minimum 12
    var best = /^(?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[^\w\*])\S{12,}$/;

    $('input[type="password"]').on('keyup', function () {
        var password = $(this);
        var pass = password.val();
        var passLabel = $('[for="admin_password"]');
        var strength = 'Weak';
        var pclass = 'danger';
        if (best.test(pass) == true) {
            strength = 'Very Strong';
            pclass = 'success';
        } else if (better.test(pass) == true) {
            strength = 'Strong';
            pclass = 'warning';
        } else if (good.test(pass) == true) {
            strength = 'OK';
            pclass = 'warning';
        } else if (bad.test(pass) == true) {
            strength = 'Weak';
        } else {
            strength = 'Very Weak';
        }
        var popover = password.attr('data-content', strength).data('bs.popover');
        popover.setContent();
        popover.$tip.addClass(popover.options.placement).removeClass('danger success info warning primary').addClass(pclass);
    });

    $('input[data-toggle="popover"]').popover({
        title: 'Password strength',
        placement: 'top',
        trigger: 'focus'
    });

// ----- END http://bootsnipp.com/snippets/featured/password-strength-popover -----

    // tabs (welcome page)
    $('ul.nav a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
});