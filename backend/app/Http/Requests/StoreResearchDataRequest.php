<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreResearchDataRequest - Centralized validation untuk POST research project
 * 
 * ALUR KERJA:
 * 1. Validasi input research project submission
 * 2. Termasuk validasi relasi ke user (PI), dates, collaborators
 * 3. Automatic error responses jika validation gagal
 * 4. Support nested validation untuk collaborators array
 */
class StoreResearchDataRequest extends FormRequest
{
    /**
     * Authorize check
     */
    public function authorize(): bool
    {
        // User sudah authenticated via middleware
        return true;
    }

    /**
     * Validation rules untuk research project
     * 
     * RULES PENJELASAN:
     * 
     * 'title' => 'required|string|max:255':
     *   - required: judul project wajib ada
     *   - string: harus string, bukan integer/array
     *   - max:255: batasan sesuai database column
     * 
     * 'description' => 'nullable|string|max:1000':
     *   - nullable: boleh kosong (optional)
     *   - string: jika ada, harus string
     *   - max:1000: detail description, max 1000 chars
     * 
     * 'start_date' => 'required|date|before_or_equal:today':
     *   - required: start date wajib
     *   - date: valid format
     *   - before_or_equal:today: tidak bisa future date (project already started)
     * 
     * 'end_date' => 'nullable|date|after:start_date':
     *   - nullable: boleh tidak ada
     *   - date: valid format
     *   - after:start_date: end date harus lebih besar dari start_date
     * 
     * 'category' => 'required|in:technology,agriculture,energy,sustainability,other':
     *   - required: wajib ada
     *   - in: hanya dari enum yang didefinisikan
     * 
     * 'principal_investigator_id' => 'required|exists:users,id':
     *   - required: PI wajib dipilih
     *   - exists: user dengan ID ini harus ada di tabel users
     *   - Validation ini prevent foreign key constraint violation
     * 
     * 'budget' => 'nullable|integer|min:0':
     *   - nullable: opsional
     *   - integer: harus angka bulat
     *   - min:0: tidak boleh negative
     * 
     * 'collaborators' => 'nullable|array':
     *   - nullable: boleh tidak ada collaborators
     *   - array: jika ada, harus array
     * 
     * 'collaborators.*.collaborator_name' => 'required|string|max:255':
     *   - asterisk (*) berarti rule apply ke SETIAP item dalam array
     *   - Setiap collaborator wajib punya nama
     * 
     * 'collaborators.*.institution' => 'nullable|string|max:255':
     *   - Setiap collaborator boleh ada institution
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date|before_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'category' => 'required|in:technology,agriculture,energy,sustainability,other',
            'principal_investigator_id' => 'required|exists:users,id',
            'budget' => 'nullable|integer|min:0',
            'collaborators' => 'nullable|array',
            'collaborators.*.collaborator_name' => 'required|string|max:255',
            'collaborators.*.institution' => 'nullable|string|max:255',
            'collaborators.*.role' => 'nullable|string|max:100',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul penelitian wajib diisi',
            'title.max' => 'Judul penelitian maksimal 255 karakter',
            'description.max' => 'Deskripsi penelitian maksimal 1000 karakter',
            'start_date.required' => 'Tanggal mulai penelitian wajib diisi',
            'start_date.date' => 'Format tanggal mulai tidak valid',
            'start_date.before_or_equal' => 'Tanggal mulai tidak boleh di masa depan',
            'end_date.date' => 'Format tanggal selesai tidak valid',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'category.required' => 'Kategori penelitian wajib dipilih',
            'category.in' => 'Kategori penelitian tidak valid',
            'principal_investigator_id.required' => 'Ketua peneliti (PI) wajib dipilih',
            'principal_investigator_id.exists' => 'Ketua peneliti yang dipilih tidak ditemukan',
            'budget.integer' => 'Budget harus angka bulat',
            'budget.min' => 'Budget tidak boleh negatif',
            'collaborators.array' => 'Kolaborator harus berupa array',
            'collaborators.*.collaborator_name.required' => 'Nama kolaborator wajib diisi',
            'collaborators.*.collaborator_name.max' => 'Nama kolaborator maksimal 255 karakter',
            'collaborators.*.institution.max' => 'Nama institusi maksimal 255 karakter',
            'collaborators.*.role.max' => 'Peran kolaborator maksimal 100 karakter',
        ];
    }

    /**
     * Prepare data untuk validation
     * 
     * ALUR:
     * 1. Convert budget ke integer jika string
     * 2. Ensure array format untuk collaborators
     */
    protected function prepareForValidation(): void
    {
        // Convert budget ke integer
        if ($this->budget && is_string($this->budget)) {
            $this->merge([
                'budget' => (int) str_replace(['Rp', '.', ','], '', $this->budget),
            ]);
        }

        // Ensure collaborators adalah array, jika tidak berikan empty array
        if (!$this->has('collaborators')) {
            $this->merge([
                'collaborators' => [],
            ]);
        }
    }
}