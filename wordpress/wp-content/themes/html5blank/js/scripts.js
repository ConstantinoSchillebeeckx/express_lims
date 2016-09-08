

/* Clean up JS version of $db

By default, the $db obj protected attributes will have a prepended '*' in the key, see:
https://ocramius.github.io/blog/fast-php-object-to-array-conversion/

This function cleans up the object so that it's easier to work with

Parameters:
- obj: <?php echo get_db()->asJSON(); ?>

*/
function cleanDB(obj) {

    var clean = {};
    var value;

    for (var k in obj) {

        value = obj[k];

        // if obj, recurse
        if (typeof obj[k] == "object" && obj[k] !== null) {
            value = cleanDB(obj[k]);
        } 

        clean[k.replace('\0*\0','')] = value;
    }
    return clean;

}
