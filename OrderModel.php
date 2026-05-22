<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = ['nomor_order', 'nama_customer', 'no_whatsapp', 'alamat', 'catatan', 'total_harga', 'status'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getOrders($status = null)
    {
        $builder = $this->orderBy('created_at', 'DESC');
        if ($status) {
            $builder->where('status', $status);
        }
        return $builder->findAll();
    }

    public function getOrderByNomor($nomorOrder)
    {
        return $this->where('nomor_order', $nomorOrder)->first();
    }

    public function generateNomorOrder()
    {
        $date = date('Ymd');
        $lastOrder = $this->like('nomor_order', 'ORD-' . $date, 'after')
                         ->orderBy('id', 'DESC')
                         ->first();

        if ($lastOrder) {
            $lastNumber = (int)substr($lastOrder['nomor_order'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'ORD-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function countByStatus($status)
    {
        return $this->where('status', $status)->countAllResults();
    }
}
