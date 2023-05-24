// Função para obter o número total de visualizações do autor por postagem
function get_total_views_by_author($author_id, $month, $year) {
    global $wpdb;

    $views = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(meta_value) 
            FROM $wpdb->postmeta
            INNER JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID
            WHERE $wpdb->postmeta.meta_key = 'views' 
            AND $wpdb->posts.post_type = 'post'
            AND $wpdb->posts.post_status = 'publish'
            AND $wpdb->posts.post_author = %d
            AND YEAR($wpdb->posts.post_date) = %d
            AND MONTH($wpdb->posts.post_date) = %d",
            $author_id,
            $year,
            $month
        )
    );

    return $views ? intval($views) : 0;
}

// Função para exibir os contadores de visualizações por autor na página de administração
function exibir_contadores_visualizacoes_admin() {
    $authors = get_users(array('has_published_posts' => true));
    $selected_author = isset($_GET['author']) ? $_GET['author'] : '';
    $selected_month = isset($_GET['month']) ? $_GET['month'] : '';
    $selected_year = isset($_GET['year']) ? $_GET['year'] : '';

    // Obtém os meses e anos disponíveis para o filtro
    $months = range(1, 12);
    $years = range(date('Y'), 2020);

    // Filtra as visualizações com base no autor e mês/ano selecionados
    $filtered_views = array();

    foreach ($authors as $author) {
        $author_id = $author->ID;

        $author_views = array();

        foreach ($months as $month) {
            foreach ($years as $year) {
                $count = get_total_views_by_author($author_id, $month, $year);
                $author_views[sprintf("%02d_%d", $month, $year)] = $count ?: 0;
            }
        }

        if ($selected_author && $author_id !== $selected_author) {
            continue;
        }

        if ($selected_month && $selected_year) {
            $filtered_views[$author_id] = array(sprintf("%02d_%d", $selected_month, $selected_year) => $author_views[sprintf("%02d_%d", $selected_month, $selected_year)]);
        } else {
            $filtered_views[$author_id] = $author_views;
        }
    }

    // Exibe a tabela de contadores de visualizações por autor
    ?>
    <div class="wrap">
        <h1>Contadores de Visualizações por Autor</h1>
        <form method="get" action="">
            <label for="author">Autor:</label>
            <select name="author" id="author">
                <option value="">Todos</option>
                <?php foreach ($authors as $author) { ?>
                    <option value="<?php echo $author->ID; ?>" <?php selected($selected_author, $author->ID); ?>><?php echo $author->display_name; ?></option>
                <?php } ?>
            </select>
            <label for="month">Mês:</label>
            <select name="month" id="month">
                <option value="">Todos</
option>
<?php foreach ($months as $month) { ?>
<option value="<?php echo $month; ?>" <?php selected($selected_month, $month); ?>><?php echo date_i18n('F', mktime(0, 0, 0, $month, 1)); ?></option>
<?php } ?>
</select>
<label for="year">Ano:</label>
<select name="year" id="year">
<option value="">Todos</option>
<?php foreach ($years as $year) { ?>
<option value="<?php echo $year; ?>" <?php selected($selected_year, $year); ?>><?php echo $year; ?></option>
<?php } ?>
</select>
<input type="submit" class="button" value="Filtrar">
</form>
<table class="wp-list-table widefat striped">
<thead>
<tr>
<th>Autor</th>
<?php foreach ($years as $year) { ?>
<?php foreach ($months as $month) { ?>
<th><?php echo date_i18n('F Y', mktime(0, 0, 0, $month, 1, $year)); ?></th>
<?php } ?>
<?php } ?>
</tr>
</thead>
<tbody>
<?php foreach ($authors as $author) { ?>
<?php $author_id = $author->ID; ?>
<tr>
<td><?php echo $author->display_name; ?></td>
<?php foreach ($years as $year) { ?>
<?php foreach ($months as $month) { ?>
<?php $month_year = sprintf("%02d_%d", $month, $year); ?>
<td><?php echo $filtered_views[$author_id][$month_year]; ?></td>
<?php } ?>
<?php } ?>
</tr>
<?php } ?>
</tbody>
</table>
</div>
<?php
}

// Adicione o seguinte código ao seu tema ou plugin para exibir a tabela de contadores de visualizações por autor
add_action('admin_menu', function() {
add_menu_page('Contadores de Visualizações', 'Contadores de Visualizações', 'manage_options', 'contadores_visualizacoes', 'exibir_contadores_visualizacoes_admin');
});