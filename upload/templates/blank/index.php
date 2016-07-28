<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <?php echo CAT_Helper_Page::getFrontendHeaders(); ?>
</head>
<body>
    <?php echo CAT_Page::getInstance($page_id)->getPageContent(1); ?>
    <?php echo CAT_Helper_Page::getFrontendFooters(); ?>
</body>
</html>