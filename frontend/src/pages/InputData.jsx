import { useState } from 'react';
import { DashboardLayout } from './DashboardLayout';
import { useApiWithAuth } from '../hooks/useApiWithAuth';
import { Send, FileText, CheckCircle, AlertCircle } from 'lucide-react';
import { useNavigate } from 'react-router-dom';


export default function InputData() {
  const navigate = useNavigate();
  const { fetchWithAuth } = useApiWithAuth();
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({
    metric_name: '',
    category: '',
    record_date: new Date().toISOString().split('T')[0],
    value: '',
    unit: '',
    target_value: '',
    notes: '',
  });

  const handleChange = (field, value) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      const json = await fetchWithAuth('/internal/sustainability-data', {
        method: 'POST',
        body: JSON.stringify({
          ...formData,
          status: 'pending',
        }),
      });
      if (!json) return;

      setSubmitted(true);
      setTimeout(() => {
        navigate('/riwayat');
      }, 2000);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleNavigate = (key) => console.log('Navigate to:', key);

  return (
    <DashboardLayout activeKey="input" onNavigate={handleNavigate}>
      <div className="flex items-center gap-3 mb-1">
        <FileText size={24} className="text-emerald-600" />
        <h1 className="text-2xl font-bold text-gray-900">Input Data Baru</h1>
      </div>
      <p className="text-sm text-gray-500 mb-8">
        Data yang diinput akan masuk ke antrian review Admin sebelum ditampilkan di dashboard.
      </p>

      {submitted && (
        <div className="mb-6 flex items-center gap-3 px-5 py-4 bg-emerald-50 border border-emerald-200 rounded-xl">
          <CheckCircle size={20} className="text-emerald-600" />
          <div>
            <p className="text-sm font-bold text-emerald-800">Data berhasil dikirim!</p>
            <p className="text-xs text-emerald-600">Menunggu review Admin. Mengalihkan ke Riwayat...</p>
          </div>
        </div>
      )}

      {error && (
        <div className="mb-6 flex items-center gap-3 px-5 py-4 bg-red-50 border border-red-200 rounded-xl">
          <AlertCircle size={20} className="text-red-500" />
          <p className="text-sm text-red-700">{error}</p>
        </div>
      )}

      <form onSubmit={handleSubmit} className="bg-white border border-gray-100 rounded-xl p-6 space-y-5 max-w-2xl">
        {/* Nama Metrik */}
        <div>
          <label className="block text-sm font-semibold text-gray-700 mb-1.5">
            Nama Metrik <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            required
            value={formData.metric_name}
            onChange={(e) => handleChange('metric_name', e.target.value)}
            placeholder="cth: Emisi CO2 Sektor A, Debit Air Bersih Unit 3"
            className="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
          />
        </div>

        {/* Kategori + Tanggal */}
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              Kategori <span className="text-red-500">*</span>
            </label>
            <select
              required
              value={formData.category}
              onChange={(e) => handleChange('category', e.target.value)}
              className="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white transition-all"
            >
              <option value="">Pilih kategori...</option>
              <option value="energy">Energy</option>
              <option value="water">Water</option>
              <option value="waste">Waste</option>
              <option value="emissions">Emissions</option>
              <option value="social">Social</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              Tanggal Pengukuran <span className="text-red-500">*</span>
            </label>
            <input
              type="date"
              required
              value={formData.record_date}
              onChange={(e) => handleChange('record_date', e.target.value)}
              className="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
            />
          </div>
        </div>

        {/* Nilai + Satuan + Target */}
        <div className="grid grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              Nilai <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              step="any"
              required
              value={formData.value}
              onChange={(e) => handleChange('value', e.target.value)}
              placeholder="412"
              className="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">
              Satuan <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              required
              value={formData.unit}
              onChange={(e) => handleChange('unit', e.target.value)}
              placeholder="ppm, MWh, m³"
              className="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
            />
          </div>
          <div>
            <label className="block text-sm font-semibold text-gray-700 mb-1.5">Target (opsional)</label>
            <input
              type="number"
              step="any"
              value={formData.target_value}
              onChange={(e) => handleChange('target_value', e.target.value)}
              placeholder="400"
              className="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
            />
          </div>
        </div>

        {/* Catatan */}
        <div>
          <label className="block text-sm font-semibold text-gray-700 mb-1.5">Catatan Tambahan</label>
          <textarea
            rows={3}
            value={formData.notes}
            onChange={(e) => handleChange('notes', e.target.value)}
            placeholder="Deskripsi kondisi lapangan saat pengukuran..."
            className="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none transition-all"
          />
        </div>

        <div className="flex items-center gap-4 pt-2">
          <button
            type="submit"
            disabled={loading || submitted}
            className="flex items-center gap-2 px-6 py-2.5 bg-emerald-700 text-white rounded-lg text-sm font-semibold hover:bg-emerald-800 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? (
              <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
            ) : (
              <Send size={16} />
            )}
            Kirim ke Review Admin
          </button>
          <span className="text-xs text-gray-400">Status otomatis "Pending" sampai divalidasi.</span>
        </div>
      </form>
    </DashboardLayout>
  );
}