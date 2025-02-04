<div class="paginacion">
    <?php if ($current_page > 1) {
        $first_page = 1;
        ?>
        <a
            href="?page=<?= $first_page ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">
            Primera &laquo;
        </a>
        <a
            href="?page=<?= $current_page - 1 ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">
            &laquo; Anterior
        </a>
    <?php } ?>

    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
        <a class="<?= ($i == $current_page) ? 'activo' : '' ?>"
            href="?page=<?= $i ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">
            <?= $i ?>
        </a>
    <?php } ?>

    <?php if ($current_page < $total_pages) {
        $last_page = $total_pages;
        ?>
        <a
            href="?page=<?= $current_page + 1 ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">
            Siguiente &raquo;
        </a>
        <a
            href="?page=<?= $last_page ?>&caseta=<?= htmlspecialchars($_GET['caseta'] ?? '') ?>&lang=<?= htmlspecialchars(get_language()) ?>">
            Última &raquo;
        </a>
    <?php } ?>
</div>