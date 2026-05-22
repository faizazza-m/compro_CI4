<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;

class Order extends BaseController
{
    protected $productModel;
    protected $orderModel;
    protected $orderDetailModel;

    public function __construct()
    {
        $this->productModel     = new ProductModel();
        $this->orderModel       = new OrderModel();
        $this->orderDetailModel = new OrderDetailModel();
    }

    public function create($productSlug)
    {
        $product = $this->productModel->getProductBySlug($productSlug);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        if ($product['stok'] <= 0) {
            return redirect()->to('/produk/' . $productSlug)->with('error', 'Maaf, stok produk habis.');
        }

        $data = [
            'title'   => 'Form Pemesanan',
            'product' => $product,
        ];

        return view('frontend/order/form', $data);
    }

    public function store()
    {
        $rules = [
            'product_id'    => 'required|integer',
            'nama_customer' => 'required|min_length[2]|max_length[255]',
            'no_whatsapp'   => 'required|min_length[10]|max_length[20]',
            'alamat'        => 'required|min_length[5]',
            'jumlah'        => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $productId = $this->request->getPost('product_id');
        $jumlah    = (int)$this->request->getPost('jumlah');

        $product = $this->productModel->find($productId);

        if (!$product || $product['status'] !== 'aktif') {
            return redirect()->back()->withInput()->with('error', 'Produk tidak ditemukan atau tidak aktif.');
        }

        if ($product['stok'] < $jumlah) {
            return redirect()->back()->withInput()->with('error', 'Stok tidak mencukupi. Stok tersedia: ' . $product['stok']);
        }

        $subtotal    = $product['harga'] * $jumlah;
        $nomorOrder  = $this->orderModel->generateNomorOrder();

        // Begin transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // Insert order
        $this->orderModel->save([
            'nomor_order'    => $nomorOrder,
            'nama_customer'  => $this->request->getPost('nama_customer'),
            'no_whatsapp'    => $this->request->getPost('no_whatsapp'),
            'alamat'         => $this->request->getPost('alamat'),
            'catatan'        => $this->request->getPost('catatan'),
            'total_harga'    => $subtotal,
            'status'         => 'pending',
        ]);

        $orderId = $this->orderModel->getInsertID();

        // Insert order detail
        $this->orderDetailModel->save([
            'order_id'    => $orderId,
            'product_id'  => $productId,
            'nama_produk' => $product['nama_produk'],
            'harga'       => $product['harga'],
            'jumlah'      => $jumlah,
            'subtotal'    => $subtotal,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        // Reduce stock
        $this->productModel->update($productId, [
            'stok' => $product['stok'] - $jumlah,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memproses pesanan.');
        }

        return redirect()->to('/order/success/' . $nomorOrder);
    }

    public function success($nomorOrder)
    {
        $order = $this->orderModel->getOrderByNomor($nomorOrder);

        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pesanan tidak ditemukan.');
        }

        $details = $this->orderDetailModel->getDetailsByOrderId($order['id']);

        // Build WhatsApp message
        $waMessage = "Halo, saya ingin konfirmasi pesanan:\n";
        $waMessage .= "📋 No. Order: " . $order['nomor_order'] . "\n";
        $waMessage .= "👤 Nama: " . $order['nama_customer'] . "\n";
        $waMessage .= "📱 WA: " . $order['no_whatsapp'] . "\n";
        $waMessage .= "📍 Alamat: " . $order['alamat'] . "\n\n";
        $waMessage .= "🛒 Produk:\n";
        foreach ($details as $detail) {
            $waMessage .= "- " . $detail['nama_produk'] . " x" . $detail['jumlah'] . " = Rp " . number_format($detail['subtotal'], 0, ',', '.') . "\n";
        }
        $waMessage .= "\n💰 Total: Rp " . number_format($order['total_harga'], 0, ',', '.') . "\n";
        if ($order['catatan']) {
            $waMessage .= "📝 Catatan: " . $order['catatan'] . "\n";
        }

        // Default admin WA number — change this to the actual admin number
        $adminWA = '6281234567890';
        $waLink  = 'https://wa.me/' . $adminWA . '?text=' . urlencode($waMessage);

        $data = [
            'title'   => 'Pesanan Berhasil',
            'order'   => $order,
            'details' => $details,
            'waLink'  => $waLink,
        ];

        return view('frontend/order/success', $data);
    }
}
