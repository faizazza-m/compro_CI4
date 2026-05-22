<?php

namespace App\Controllers;

use App\Models\ProductCategoryModel;

class AdminKategori extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new ProductCategoryModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Kelola Kategori',
            'categories' => $this->categoryModel->getCategoriesWithCount(),
        ];

        return view('admin/kategori/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Kategori',
        ];

        return view('admin/kategori/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama_kategori' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaKategori = $this->request->getPost('nama_kategori');
        $slug = url_title($namaKategori, '-', true);

        // Ensure unique slug
        $existing = $this->categoryModel->getCategoryBySlug($slug);
        if ($existing) {
            $slug .= '-' . time();
        }

        $this->categoryModel->save([
            'nama_kategori' => $namaKategori,
            'slug'          => $slug,
            'deskripsi'     => $this->request->getPost('deskripsi'),
        ]);

        return redirect()->to('/admin/kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        $data = [
            'title'    => 'Edit Kategori',
            'category' => $category,
        ];

        return view('admin/kategori/form', $data);
    }

    public function update($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        $rules = [
            'nama_kategori' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaKategori = $this->request->getPost('nama_kategori');
        $slug = url_title($namaKategori, '-', true);

        // Ensure unique slug (excluding current)
        $existing = $this->categoryModel->where('slug', $slug)->where('id !=', $id)->first();
        if ($existing) {
            $slug .= '-' . time();
        }

        $this->categoryModel->update($id, [
            'nama_kategori' => $namaKategori,
            'slug'          => $slug,
            'deskripsi'     => $this->request->getPost('deskripsi'),
        ]);

        return redirect()->to('/admin/kategori')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan.');
        }

        $this->categoryModel->delete($id);

        return redirect()->to('/admin/kategori')->with('success', 'Kategori berhasil dihapus.');
    }
}
