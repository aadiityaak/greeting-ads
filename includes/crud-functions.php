<?php
// Fungsi untuk menambah data
function greeting_ads_add_data($data)
{
  global $wpdb;
  $table_name = $wpdb->prefix . GREETING_ADS_TABLE;
  return $wpdb->insert($table_name, $data);
}

// Fungsi untuk mengupdate data
function greeting_ads_update_data($id, $data)
{
  global $wpdb;
  $table_name = $wpdb->prefix . GREETING_ADS_TABLE;

  // Hilangkan karakter escape (\) dari data
  $data = array_map(function ($value) {
    return stripslashes(sanitize_text_field($value));
  }, $data);

  // Update data di database
  return $wpdb->update(
    $table_name,
    $data,
    array('id' => $id),
    array('%s', '%s', '%s', '%s', '%s'), // Format data
    array('%d') // Format where
  );
}

// Fungsi untuk menghapus data
function greeting_ads_delete_data($id)
{
  global $wpdb;
  $table_name = $wpdb->prefix . GREETING_ADS_TABLE;
  return $wpdb->delete($table_name, array('id' => $id));
}


// ajax handler
add_action('wp_ajax_search_greeting', 'handle_search_greeting');

function handle_search_greeting()
{
  global $wpdb;
  $table_name = $wpdb->prefix . GREETING_ADS_TABLE;

  // Ambil parameter dari request
  $id_grup_iklan = sanitize_text_field($_POST['id_grup_iklan']);
  $nomor_kata_kunci = sanitize_text_field($_POST['nomor_kata_kunci']);

  // Query database untuk mencari data
  $query = $wpdb->prepare(
    "SELECT greeting FROM $table_name WHERE id_grup_iklan = %s AND nomor_kata_kunci = %s",
    $id_grup_iklan,
    $nomor_kata_kunci
  );

  $result = $wpdb->get_row($query, ARRAY_A);

  if ($result) {
    wp_send_json_success(['greeting' => $result['greeting']]);
  } else {
    wp_send_json_error(['message' => 'Data tidak ditemukan.']);
  }

  wp_die(); // Penting untuk mengakhiri proses AJAX
}


add_action('wp_ajax_delete_greeting', 'handle_delete_greeting');

function handle_delete_greeting()
{
  global $wpdb;
  $table_name = $wpdb->prefix . GREETING_ADS_TABLE;

  // Ambil ID dari permintaan AJAX
  $id = intval($_POST['id']);

  // Hapus data dari database
  $result = $wpdb->delete(
    $table_name,
    array('id' => $id),
    array('%d') // Format where
  );

  if ($result) {
    wp_send_json_success('Data berhasil dihapus.');
  } else {
    wp_send_json_error('Gagal menghapus data.');
  }
}
