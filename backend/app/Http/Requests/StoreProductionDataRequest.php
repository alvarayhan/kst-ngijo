<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreProductionDataRequest - Centralized validation untuk POST production data
 * 
 * ALUR KERJA:
 * 1. Validasi input sesuai Laravel FormRequest pattern
 * 2. Rules defined centrally, bisa di-reuse dan maintain lebih mudah
 * 3. Automatic error responses jika validation gagal (422 Unprocessable Entity)
 * 4. Access validated data via $this->validated() di controller
 * 
 * KEUNTUNGAN:
 * - Separation of concerns: validation logic terpisah dari controller
 * - Reusable: bisa pakai di update/store dengan rules berbeda
 * - Clean: Controller jadi lebih fokus ke business logic
 * - Error handling: automatic return JSON error response
 */
class StoreProductionDataRequest extends FormRequest
{
    /**
     * Authorize check - whether user is authorized untuk submit form ini
     * 
     * ALUR:
     * 1. Return true jika user authorized (sudah logged in via middleware)
     * 2. Return false jika unauthorized (403 Forbidden)
     * 
     * CATATAN: Middleware 'auth:api' sudah menjamin user authenticated
     * jadi kita bisa return true di sini
     */
    public function authorize(): bool
    {
        // User sudah di-authenticate via middleware, jadi authorized
        return true;
    }

    /**
     * Validation rules untuk production data submission
     * 
     * RULES PENJELASAN:
     * 
     * 'date' => 'required|date':
     *   - required: wajib ada
     *   - date: harus format date yang valid (Y-m-d)
     * 
     * 'visitor_count' => 'required|integer|min:0':
     *   - required: wajib ada
     *   - integer: harus integer, bukan string/float
     *   - min:0: tidak bisa negative
     * 
     * 'visitor_category' => 'required|in:individuals,groups,researchers,students':
     *   - required: wajib ada
     *   - in: hanya boleh nilai dari enum yang ditentukan
     *   - Ini memastikan hanya valid category yang diterima
     * 
     * 'time_slot' => 'required|in:morning,afternoon,evening':
     *   - required: wajib ada
     *   - in: limited ke 3 time slot saja (consistent dengan database enum)
     * 
     * 'notes' => 'nullable|string|max:1000':
     *   - nullable: boleh kosong/tidak ada di request
     *   - string: jika ada, harus string
     *   - max:1000: maximum 1000 characters untuk notes
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'visitor_count' => 'required|integer|min:0',
            'visitor_category' => 'required|in:individuals,groups,researchers,students',
            'time_slot' => 'required|in:morning,afternoon,evening',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom error messages untuk validation failures
     * 
     * ALUR:
     * 1. Jika validation gagal, return custom message ini
     * 2. Membuat error message lebih user-friendly
     * 3. Bisa di-customize per rule atau per field
     * 
     * FORMAT: field.rule => message
     * Contoh: date.required => message ketika date tidak diberikan
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal data wajib diisi',
            'date.date' => 'Format tanggal tidak valid (gunakan: YYYY-MM-DD)',
            'visitor_count.required' => 'Jumlah pengunjung wajib diisi',
            'visitor_count.integer' => 'Jumlah pengunjung harus angka bulat',
            'visitor_count.min' => 'Jumlah pengunjung tidak boleh negatif',
            'visitor_category.required' => 'Kategori pengunjung wajib dipilih',
            'visitor_category.in' => 'Kategori pengunjung tidak valid',
            'time_slot.required' => 'Waktu pengunjungan wajib dipilih',
            'time_slot.in' => 'Waktu pengunjungan harus: morning, afternoon, atau evening',
            'notes.string' => 'Catatan harus berupa teks',
            'notes.max' => 'Catatan tidak boleh lebih dari 1000 karakter',
        ];
    }

    /**
     * Prepare data untuk validation
     * 
     * ALUR:
     * 1. Hook yang dipanggil sebelum validation
     * 2. Bisa digunakan untuk sanitize/transform input
     * 3. Contoh: convert string "10" ke integer 10
     * 
     * CATATAN: Jarang digunakan, tapi useful untuk data cleaning
     */
    protected function prepareForValidation(): void
    {
        // Convert visitor_count ke integer jika string
        if (is_string($this->visitor_count)) {
            $this->merge([
                'visitor_count' => (int) $this->visitor_count,
            ]);
        }

        // Lowercase visitor_category dan time_slot untuk consistency
        if ($this->visitor_category) {
            $this->merge([
                'visitor_category' => strtolower($this->visitor_category),
            ]);
        }

        if ($this->time_slot) {
            $this->merge([
                'time_slot' => strtolower($this->time_slot),
            ]);
        }
    }
}