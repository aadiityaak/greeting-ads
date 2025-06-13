<?php
global $wpdb;
$table_name = $wpdb->prefix . GREETING_ADS_TABLE;

// Handle impor CSV
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_csv'])) {
  greeting_ads_import_csv();
}

// Handle tambah/edit/hapus data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['add_data'])) {
    $data = array(
      'kata_kunci' => sanitize_text_field($_POST['kata_kunci']),
      'grup_iklan' => sanitize_text_field($_POST['grup_iklan']),
      'id_grup_iklan' => sanitize_text_field($_POST['id_grup_iklan']),
      'nomor_kata_kunci' => sanitize_text_field($_POST['nomor_kata_kunci']),
      'greeting' => sanitize_text_field($_POST['greeting']),
    );
    greeting_ads_add_data($data);
  } elseif (isset($_POST['update_data'])) {
    $data = array(
      'kata_kunci' => sanitize_text_field($_POST['kata_kunci']),
      'grup_iklan' => sanitize_text_field($_POST['grup_iklan']),
      'id_grup_iklan' => sanitize_text_field($_POST['id_grup_iklan']),
      'nomor_kata_kunci' => sanitize_text_field($_POST['nomor_kata_kunci']),
      'greeting' => sanitize_text_field($_POST['greeting']),
    );
    greeting_ads_update_data($_POST['id'], $data);
  }
  $edit_id = isset($_GET['edit']) ? $_GET['edit'] : '';
}

// Ambil semua data dari database
$per_page = 100; // Jumlah baris per halaman
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1; // Halaman aktif
$offset = ($current_page - 1) * $per_page;

// Total jumlah data
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Data untuk halaman saat ini
$search_kata_kunci = isset($_GET['search_kata_kunci']) ? sanitize_text_field($_GET['search_kata_kunci']) : '';
$search_nomor_kata_kunci = isset($_GET['search_nomor_kata_kunci']) ? sanitize_text_field($_GET['search_nomor_kata_kunci']) : '';
$search_greeting = isset($_GET['search_greeting']) ? sanitize_text_field($_GET['search_greeting']) : '';
$where_clauses = [];
$params = [];

if (!empty($search_kata_kunci)) {
  $where_clauses[] = "kata_kunci LIKE %s";
  $params[] = '%' . $wpdb->esc_like($search_kata_kunci) . '%';
}
if (!empty($search_nomor_kata_kunci)) {
  $where_clauses[] = "nomor_kata_kunci LIKE %s";
  $params[] = '%' . $wpdb->esc_like($search_nomor_kata_kunci) . '%';
}
if (!empty($search_greeting)) {
  $where_clauses[] = "greeting LIKE %s";
  $params[] = '%' . $wpdb->esc_like($search_greeting) . '%';
}

$where_sql = '';
if (!empty($where_clauses)) {
  $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

$total_items_query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name $where_sql", ...$params);
$total_items = $wpdb->get_var($total_items_query);


$params[] = $per_page;
$params[] = $offset;
$query = "SELECT * FROM $table_name $where_sql ORDER BY id DESC LIMIT %d OFFSET %d";

$data = $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A);

// Untuk edit, cari data berdasarkan ID
$edit_data = null;
if (isset($_GET['edit'])) {
  $edit_id = intval($_GET['edit']); // Pastikan ID adalah integer
  foreach ($data as $row) {
    if ($row['id'] == $edit_id) {
      $edit_data = $row;
      break;
    }
  }
}
?>

<div class="wrap">

  <div class="card" style="max-width: 100% !important;">
    <h2 class="title">Pencarian Greeting</h2>
    <form id="search-form" class="form-wrap">
      <table class="form-table">
        <tr>
          <th scope="row"><label for="search_id_grup_iklan">utm_content</label></th>
          <td><input type="text" name="id_grup_iklan" id="search_id_grup_iklan" class="regular-text" required></td>
        </tr>
        <tr>
          <th scope="row"><label for="search_nomor_kata_kunci">utm_medium</label></th>
          <td><input type="text" name="nomor_kata_kunci" id="search_nomor_kata_kunci" class="regular-text" required></td>
        </tr>
      </table>
      <button type="submit" class="button button-primary">Cari</button>
    </form>

    <!-- Output hasil pencarian -->
    <div id="search-result"></div>
  </div>

  <!-- Form impor CSV -->
  <div class="card" style="max-width: 100% !important;">
    <h2 class="title">Impor Data CSV</h2>
    <form method="post" enctype="multipart/form-data" class="form-wrap">
      <input type="file" name="csv_file" accept=".csv" required>
      <?php submit_button('Impor', 'primary', 'import_csv'); ?>
    </form>
  </div>

  <!-- Form tambah/edit data -->
  <div class="card" style="max-width: 100% !important;">
    <h2 class="title"><?php echo isset($_GET['edit']) ? 'Edit Data' : 'Tambah Data'; ?></h2>
    <form method="post" class="form-wrap">
      <input type="hidden" name="id" value="<?php echo isset($edit_data) ? esc_attr($edit_data['id']) : ''; ?>">
      <table class="form-table">
        <tr>
          <th scope="row"><label for="kata_kunci">Kata Kunci</label></th>
          <td><input type="text" name="kata_kunci" id="kata_kunci" value="<?php echo isset($edit_data) ? esc_attr($edit_data['kata_kunci']) : ''; ?>" class="regular-text" required></td>
        </tr>
        <tr>
          <th scope="row"><label for="grup_iklan">Grup Iklan</label></th>
          <td><input type="text" name="grup_iklan" id="grup_iklan" value="<?php echo isset($edit_data) ? esc_attr($edit_data['grup_iklan']) : ''; ?>" class="regular-text" required></td>
        </tr>
        <tr>
          <th scope="row"><label for="id_grup_iklan">ID Grup Iklan</label></th>
          <td><input type="text" name="id_grup_iklan" id="id_grup_iklan" value="<?php echo isset($edit_data) ? esc_attr($edit_data['id_grup_iklan']) : ''; ?>" class="regular-text" required></td>
        </tr>
        <tr>
          <th scope="row"><label for="nomor_kata_kunci">Nomor Kata Kunci</label></th>
          <td><input type="text" name="nomor_kata_kunci" id="nomor_kata_kunci" value="<?php echo isset($edit_data) ? esc_attr($edit_data['nomor_kata_kunci']) : ''; ?>" class="regular-text" required></td>
        </tr>
        <tr>
          <th scope="row"><label for="greeting">Greeting</label></th>
          <td><input type="text" name="greeting" id="greeting" value="<?php echo isset($edit_data) ? esc_attr($edit_data['greeting']) : ''; ?>" class="regular-text" required></td>
        </tr>
      </table>
      <?php submit_button(isset($edit_data) ? 'Update' : 'Tambah', 'primary', isset($edit_data) ? 'update_data' : 'add_data'); ?>
    </form>
  </div>

  <!-- Tabel data widefat -->
  <div class="card" style="max-width: 100% !important;">
    <h2 class="title">Data Greeting Ads</h2>
    <form method="get" class="form-wrap" style="margin-bottom: 1rem;">
      <input type="hidden" name="page" value="greeting-ads">
      <input type="text" name="search_kata_kunci" placeholder="Cari Kata Kunci" value="<?php echo isset($_GET['search_kata_kunci']) ? esc_attr($_GET['search_kata_kunci']) : ''; ?>" style="margin-right: 10px;">
      <input type="text" name="search_nomor_kata_kunci" placeholder="Cari Nomor Kata Kunci" value="<?php echo isset($_GET['search_nomor_kata_kunci']) ? esc_attr($_GET['search_nomor_kata_kunci']) : ''; ?>" style="margin-right: 10px;">
      <input type="text" name="search_greeting" placeholder="Cari Greeting" value="<?php echo isset($_GET['search_greeting']) ? esc_attr($_GET['search_greeting']) : ''; ?>" style="margin-right: 10px;">
      <input type="submit" class="button" value="Filter">
    </form>
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Kata Kunci</th>
          <th>Grup Iklan</th>
          <th>ID Grup Iklan</th>
          <th>Nomor Kata Kunci</th>
          <th>Greeting</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($data)): ?>
          <?php foreach ($data as $row): ?>
            <tr>
              <td><?php echo esc_html($row['id']); ?></td>
              <td><?php echo esc_html($row['kata_kunci']); ?></td>
              <td><?php echo esc_html($row['grup_iklan']); ?></td>
              <td><?php echo esc_html($row['id_grup_iklan']); ?></td>
              <td><?php echo esc_html($row['nomor_kata_kunci']); ?></td>
              <td><?php echo esc_html($row['greeting']); ?></td>
              <td>
                <a href="?page=greeting-ads&edit=<?php echo esc_attr($row['id']); ?>&paged=<?php echo esc_attr($current_page); ?>&per_page=<?php echo esc_attr($per_page); ?>&search_kata_kunci=<?php echo esc_attr($search_kata_kunci); ?>&search_nomor_kata_kunci=<?php echo esc_attr($search_nomor_kata_kunci); ?>" class="button button-small">Edit</a>
                <a href="#" class="button button-small button-danger delete-data" data-id="<?php echo esc_attr($row['id']); ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7">Tidak ada data.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php
    $total_pages = ceil($total_items / $per_page);
    if ($total_pages > 1): ?>
      <nav class="tablenav bottom">
        <div class="tablenav-pages">
          <span class="displaying-num"><?php echo number_format_i18n($total_items); ?> item</span>
          <span class="pagination-links">
            <?php if ($current_page > 1): ?>
              <a class="prev-page button" href="<?php echo add_query_arg(['paged' => ($current_page - 1)]); ?>">« Previous</a>
            <?php endif; ?>

            <span class="paging-input">
              <input type="number" class="current-page" value="<?php echo $current_page; ?>" min="1" max="<?php echo $total_pages; ?>" size="2">
              of <span class="total-pages"><?php echo number_format_i18n($total_pages); ?></span>
            </span>

            <?php if ($current_page < $total_pages): ?>
              <a class="next-page button" href="<?php echo add_query_arg(['paged' => ($current_page + 1)]); ?>">Next »</a>
            <?php endif; ?>
          </span>
        </div>
      </nav>
    <?php endif; ?>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const searchResult = document.getElementById('search-result');

    searchForm.addEventListener('submit', function(e) {
      e.preventDefault();

      // Ambil nilai dari input form
      const idGrupIklan = document.getElementById('search_id_grup_iklan').value;
      const nomorKataKunci = document.getElementById('search_nomor_kata_kunci').value;

      // Kirim data ke server via AJAX
      fetch(ajaxurl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            action: 'search_greeting', // Nama action untuk hook AJAX
            id_grup_iklan: idGrupIklan,
            nomor_kata_kunci: nomorKataKunci,
          }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Tampilkan hasil pencarian
            searchResult.innerHTML = `<p><strong>Greeting:</strong> ${data.data.greeting}</p>`;
          } else {
            searchResult.innerHTML = `<p>${data.message}</p>`;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          searchResult.innerHTML = `<p>Terjadi kesalahan saat mencari data.</p>`;
        });
    });
  });

  // fungsi hapus
  document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-data');

    deleteButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();

        // Ambil ID dari atribut data-id
        const id = this.getAttribute('data-id');

        // Kirim permintaan AJAX
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
              action: 'delete_greeting', // Nama action untuk hook AJAX
              id: id,
            }),
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert(data.data); // Tampilkan pesan sukses
              location.reload(); // Reload halaman untuk memperbarui tabel
            } else {
              alert(data.data); // Tampilkan pesan error
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus data.');
          });
      });
    });
  });
</script>