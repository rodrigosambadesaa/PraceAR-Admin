<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Languages</title>
    <style>
        .flags {
            display: flex;
            list-style: none;
            padding: 0;
        }

        .flags li {
            margin-right: 10px;
        }

        .flags a {
            text-decoration: none;
            color: black;

            img {
                box-shadow: 0 0 2px 1px black;
            }
        }
    </style>
</head>

<body>
    <nav style="position: fixed; right: 1rem;top: 1rem">
        <ul class="flags">
            <?php
            foreach (LANGUAGES as $key_language => $text_language):
                ?>
                <li>
                    <?php
                    $page = isset($_REQUEST['page']) ? "&page=" . $_REQUEST['page'] : '';
                    $caseta = isset($_REQUEST['caseta']) ? "&caseta=" . $_REQUEST['caseta'] : '';
                    $codigo_idioma = isset($_REQUEST['codigo_idioma']) ? "&codigo_idioma=" . $_REQUEST['codigo_idioma'] : '';
                    $id = isset($_REQUEST['id']) ? "&id=" . $_REQUEST['id'] : '';
                    ?>
                    <a href="<?= "?lang=$key_language$caseta$page$id$codigo_idioma" ?>">
                        <img width="15" height="15" src="<?= FLAG_IMAGES_URL . "$key_language.png" ?>"
                            alt="<?= $text_language ?>">
                    </a>
                </li>
                <?php
            endforeach;
            ?>
        </ul>
    </nav>
</body>

</html>