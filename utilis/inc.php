<?php 
    function start_page($title): void { ?>
<!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8" />
            <title><?php echo $title; ?></title>
        </head>
        <body>
    <?php }
?>
   
<?php function end_page(): void { ?>

    </body>
</html>
    <?php }
?>