<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreSustainabilityDataRequest - Centralized validation untuk POST sustainability data
 * 
 * ALUR KERJA:
 * 1. Validasi input sustainability metrics
 * 2. Include target value validation (harus >= actual value untuk meaningful target)
 * 3. Automatic error responses jika validation gagal
 */
class StoreSustainabilityDataRequest extends FormRequest
{
    /**
     * Authorize check
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules untuk sustainability data
     * 
     * RULES PENJELASAN:
     * 
     * 'record_date' => 'required|date|before_or_equal:today':
     *   - required: tanggal recording wajib
     *   - date: valid format
     *   - before_or_equal:today: tidak boleh future date (harus data historis)
     * 
     * 'category' => 'required|in:energy,water,waste,emissions,social':
     *   - required: kategori sustainability wajib dipilih
     *   - in: hanya dari enum yang didefinisikan di migration
     * 
     * 'metric_name' => 'required|string|max:255':
     *   - required: nama metrik wajib ada
     *   - Contoh: "Daily electricity consumption", "Water usage per unit"
     * 
     * 'value' => 'required|numeric|min:0':
     *   - required: actual value wajib ada
     *   - numeric: bisa decimal/float (15,2 di database)
     *   - min:0: nilai sustainability tidak boleh negative
     * 
     * 'unit' => 'required|string|max:50':
     *   - required: satuan/unit wajib diisi
     *   - Contoh: "kWh", "liter", "kg", "kg CO2"
     * 
     * 'target_value' => 'nullable|numeric|min:0|gte:value':
     *   - nullable: boleh tidak ada target (baseline)
     *   - numeric: decimal allowed
     *   - min:0: target tidak boleh negative
     *   - gte:value: target_value >= value (target harus realistic/achievable)
     *   - Jika target < value, berarti metrik sudah melampaui target (anomali)
     * 
     * 'notes' => 'nullable|string|max:1000':
     *   - nullable: opsional
     *   - Additional notes tentang context metrik
     */
    public function rules(): array
    {
        return [
            'record_date' => 'required|date|before_or_equal:today',
            'category' => 'required|in:energy,water,waste,emissions,social',
            'metric_name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'target_value' => 'nullable|numeric|min:0|gte:value',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'record_date.required' => 'Tanggal pencatatan wajib diisi',
            'record_date.date' => 'Format tanggal pencatatan tidak valid',
            'record_date.before_or_equal' => 'Tanggal pencatatan tidak boleh di masa depan',
            'category.required' => 'Kategori sustainability wajib dipilih',
            'category.in' => 'Kategori sustainability tidak valid',
            'metric_name.required' => 'Nama metrik wajib diisi',
            'metric_name.max' => 'Nama metrik maksimal 255 karakter',
            'value.required' => 'Nilai metrik wajib diisi',
            'value.numeric' => 'Nilai metrik harus berupa angka',
            'value.min' => 'Nilai metrik tidak boleh negatif',
            'unit.required' => 'Satuan/unit wajib diisi',
            'unit.max' => 'Satuan maksimal 50 karakter',
            'target_value.numeric' => 'Target value harus berupa angka',
            'target_value.min' => 'Target value tidak boleh negatif',
            'target_value.gte' => 'Target value harus lebih besar atau sama dengan nilai actual',
            'notes.max' => 'Catatan maksimal 1000 karakter',
        ];
    }

    /**
     * Prepare data untuk validation
     * 
     * ALUR:
     * 1. Convert value dan target_value ke float untuk consistency
     * 2. Trim whitespace dari unit
     */
    protected function prepareForValidation(): void
    {
        // Convert value ke float/decimal
        if ($this->value && is_string($this->value)) {
            $this->merge([
                'value' => (float) $this->value,
            ]);
        }

        // Convert target_value ke float jika ada
        if ($this->target_value && is_string($this->target_value)) {
            $this->merge([
                'target_value' => (float) $this->target_value,
            ]);
        }

        // Trim unit whitespace
        if ($this->unit) {
            $this->merge([
                'unit' => trim($this->unit),
            ]);
        }

        // Lowercase category untuk consistency
        if ($this->category) {
            $this->merge([
                'category' => strtolower($this->category),
            ]);
        }
    }
}