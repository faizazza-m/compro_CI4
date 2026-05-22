<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderDetailModel extends Model
{
    protected $table            = 'order_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['order_id', 'product_id', 'nama_produk', 'harga', 'jumlah', 'subtotal'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function getDetailsByOrderId($orderId)
    {
        return $this->select('order_details.*, products.gambar, products.slug')
                    ->join('products', 'products.id = order_details.product_id', 'left')
                    ->where('order_details.order_id', $orderId)
                    ->findAll();
    }
}
