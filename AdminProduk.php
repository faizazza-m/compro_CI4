<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductCategoryModel;

class AdminProduk extends BaseController
{
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel  = new ProductModel();
        $this->categoryModel = new ProductCategoryModel();
    }

    public function index()
    {
        $data = [
            'title'    => 'Kelola Produk',
            'products' => $this->productModel->getAllProducts(),
        ];

        return view('admin/produk/index', $data);
    }

    public function create()
    {
        $data = [
            'title'      => 'Tambah Produk',
            'categories' => $this->categoryModel->getCategories(),
        ];

        return view('admin/produk/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama_produk' => 'required|min_length[2]|max_length[255]',
            'category_id' => 'required|integer',
            'harga'       => 'required|numeric',
            'stok'        => 'required|integer',
        ];

        // Add image validation only if file was uploaded
        $img = $this->request->getFile('gambar');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $rules['gambar'] = 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/webp]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaProduk = $this->request->getPost('nama_produk');
        $slug = url_title($namaProduk, '-', true);

        // Ensure unique slug
        $existing = $this->productModel->where('slug', $slug)->first();
        if ($existing) {
            $slug .= '-' . time();
        }

        $gambarName = null;
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $gambarName = $img->getRandomName();
            $img->move(FCPATH . 'uploads/products', $gambarName);
        }

        $this->productModel->save([
            'category_id' => $this->request->getPost('category_id'),
            'nama_produk' => $namaProduk,
            'slug'        => $slug,
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => $this->request->getPost('harga'),
            'stok'        => $this->request->getPost('stok'),
            'gambar'      => $gambarName,
            'status'      => $this->request->getPost('status') ?? 'aktif',
        ]);

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Produk',
            'product'    => $product,
            'categories' => $this->categoryModel->getCategories(),
        ];

        return view('admin/produk/form', $data);
    }

    public function update($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        $rules = [
            'nama_produk' => 'required|min_length[2]|max_length[255]',
            'category_id' => 'required|integer',
            'harga'       => 'required|numeric',
            'stok'        => 'required|integer',
        ];

        $img = $this->request->getFile('gambar');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $rules['gambar'] = 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/webp]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaProduk = $this->request->getPost('nama_produk');
        $slug = url_title($namaProduk, '-', true);

        // Ensure unique slug (excluding current)
        $existing = $this->productModel->where('slug', $slug)->where('id !=', $id)->first();
        if ($existing) {
            $slug .= '-' . time();
        }

        $gambarName = $product['gambar'];
        if ($img && $img->isValid() && !$img->hasMoved()) {
            // Delete old image
            if ($product['gambar'] && file_exists(FCPATH . 'uploads/products/' . $product['gambar'])) {
                unlink(FCPATH . 'uploads/products/' . $product['gambar']);
            }
            $gambarName = $img->getRandomName();
            $img->move(FCPATH . 'uploads/products', $gambarName);
        }

        $this->productModel->update($id, [
            'category_id' => $this->request->getPost('category_id'),
            'nama_produk' => $namaProduk,
            'slug'        => $slug,
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => $this->request->getPost('harga'),
            'stok'        => $this->request->getPost('stok'),
            'gambar'      => $gambarName,
            'status'      => $this->request->getPost('status') ?? 'aktif',
        ]);

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil diperbarui.');
    }

    public function delete($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan.');
        }

        // Delete image file
        if ($product['gambar'] && file_exists(FCPATH . 'uploads/products/' . $product['gambar'])) {
            unlink(FCPATH . 'uploads/products/' . $product['gambar']);
        }

        $this->productModel->delete($id);

        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil dihapus.');
    }
}
