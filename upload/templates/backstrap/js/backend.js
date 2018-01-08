/* templates/backstrap/js/backend.js */
function BCGrowl(message,success) {
    if(success) {
        $.bootstrapGrowl(message);
    } else {
        $.bootstrapGrowl(message, {type: 'danger'});
    }
};