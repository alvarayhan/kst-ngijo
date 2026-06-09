import { useEffect, useState } from 'react';
import { DashboardLayout } from './DashboardLayout';
import { useApiWithAuth } from '../hooks/useApiWithAuth';
import { ClipboardList, Clock, XCircle, CheckCircle, Search, Loader2, AlertCircle } from 'lucide-react';

const STATUS_CONFIG = {
  pending:  { label: 'Pending',    bg: 'bg-orange-50', border: 'border-orange-200', text: 'text-orange-700', dot: 'bg-orange-400' },
  approved: { label: 'Disetujui',  bg: 'bg-green-50',  border: 'border-green-200',  text: 'text-green-700',  dot: 'bg-green-500' },
  rejected: { label: 'Ditolak',    bg: 'bg-red-50',    border: 'border-red-200',    text: 'text-red-700',    dot: 'bg-red-500' },
};

export default function RiwayatRevisi() {
  const { fetchWithAuth } = useApiWithAuth();
  const [dataList, setDataList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filterStatus, setFilterStatus] = useState('all');
  const [searchQuery, setSearchQuery] = useState('');

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const json = await fetchWithAuth('/internal/sustainability-data');
      if (!json) return;

      // Note: filter by user ID masih perlu, ambil dari AuthContext
      const { user } = JSON.parse(localStorage.getItem('kst_user') || '{}');
      const myData = (json.real_time_sensor_feed || []).filter(
        (d) => d.created_by_user_id === user?.id
      );
      setDataList(myData);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const counts = {
    all: dataList.length,
    pending: dataList.filter((d) => d.status === 'pending').length,
    rejected: dataList.filter((d) => d.status === 'rejected').length,
    approved: dataList.filter((d) => d.status === 'approved').length,
  };

  const filteredData = dataList.filter((d) => {
    const matchStatus = filterStatus === 'all' || d.status === filterStatus;
    const matchSearch =
      searchQuery === '' ||
      d.metric_name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
      String(d.id).includes(searchQuery);
    return matchStatus && matchSearch;
  });

  const filterButtons = [
    { key: 'all', label: 'Semua', count: counts.all },
    { key: 'pending', label: 'Pending', count: counts.pending },
    { key: 'rejected', label: 'Perlu Revisi', count: counts.rejected, highlight: counts.rejected > 0 },
    { key: 'approved', label: 'Disetujui', count: counts.approved },
  ];

  const handleNavigate = (key) => console.log('Navigate to:', key);

  return (
    <DashboardLayout activeKey="riwayat" onNavigate={handleNavigate}>
      <div className="flex items-center gap-3 mb-1">
        <ClipboardList size={24} className="text-emerald-600" />
        <h1 className="text-2xl font-bold text-gray-900">Riwayat & Revisi</h1>
      </div>
      <p className="text-sm text-gray-500 mb-6">
        Semua data yang pernah lo submit. Data yang ditolak bisa direvisi dan dikirim ulang.
      </p>

      {/* Stat Cards */}
      <div className="grid grid-cols-3 gap-4 mb-6">
        <div className="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-600">
            <Clock size={18} />
          </div>
          <div>
            <p className="text-2xl font-bold text-gray-900">{counts.pending}</p>
            <p className="text-xs text-gray-500">Menunggu Review</p>
          </div>
        </div>
        <div className={`bg-white rounded-xl p-4 flex items-center gap-3 ${counts.rejected > 0 ? 'border-2 border-red-200 ring-2 ring-red-50' : 'border border-gray-200'}`}>
          <div className="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600">
            <XCircle size={18} />
          </div>
          <div>
            <p className="text-2xl font-bold text-gray-900">{counts.rejected}</p>
            <p className={`text-xs font-semibold ${counts.rejected > 0 ? 'text-red-600' : 'text-gray-500'}`}>
              {counts.rejected > 0 ? '⚠ Perlu Revisi!' : 'Ditolak'}
            </p>
          </div>
        </div>
        <div className="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
            <CheckCircle size={18} />
          </div>
          <div>
            <p className="text-2xl font-bold text-gray-900">{counts.approved}</p>
            <p className="text-xs text-gray-500">Disetujui</p>
          </div>
        </div>
      </div>

      {/* Search + Filters */}
      <div className="flex items-center justify-between mb-6">
        <div className="relative w-80">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={15} />
          <input
            type="text"
            placeholder="Cari nama metrik atau ID..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white transition-all"
          />
        </div>
        <div className="flex items-center gap-2">
          {filterButtons.map((btn) => (
            <button
              key={btn.key}
              onClick={() => setFilterStatus(btn.key)}
              className={`px-4 py-1.5 rounded-full text-xs font-semibold transition-colors ${
                filterStatus === btn.key
                  ? btn.highlight ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'
                  : btn.highlight ? 'bg-red-50 border border-red-200 text-red-700 hover:bg-red-100'
                  : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
              }`}
            >
              {btn.label} ({btn.count})
            </button>
          ))}
        </div>
      </div>

      {/* Loading */}
      {loading && (
        <div className="flex items-center justify-center py-20 gap-3 text-gray-400">
          <Loader2 size={20} className="animate-spin" /> <span className="text-sm">Memuat riwayat...</span>
        </div>
      )}

      {/* Error */}
      {!loading && error && (
        <div className="flex items-center justify-center py-16 gap-3 text-red-500">
          <AlertCircle size={20} /> <span className="text-sm">{error}</span>
        </div>
      )}

      {/* Data Cards */}
      {!loading && !error && (
        <div className="flex flex-col gap-3 max-w-4xl">
          {filteredData.length === 0 ? (
            <div className="text-center py-16 text-gray-400 text-sm bg-white rounded-xl border border-gray-100">
              Tidak ada data dengan filter ini.
            </div>
          ) : (
            filteredData.map((item) => {
              const cfg = STATUS_CONFIG[item.status] || STATUS_CONFIG.pending;
              return (
                <div key={item.id} className={`${cfg.bg} ${cfg.border} border rounded-xl p-5`}>
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-1">
                        <div className={`w-2 h-2 rounded-full ${cfg.dot}`} />
                        <span className={`text-xs font-bold ${cfg.text} uppercase`}>{cfg.label}</span>
                        <span className="text-[11px] text-gray-400">ID: {item.id}</span>
                      </div>
                      <h3 className="text-sm font-bold text-gray-900">{item.metric_name}</h3>
                      <p className="text-xs text-gray-500 mt-1">
                        Kategori: {item.category} · Nilai: {item.value} {item.unit} · Tanggal: {item.record_date}
                      </p>
                      {item.notes && (
                        <p className="text-xs text-gray-400 mt-1">📝 {item.notes}</p>
                      )}
                      {item.status === 'rejected' && item.rejection_reason && (
                        <p className="text-xs text-red-600 mt-2 font-medium">
                          ❌ Alasan: {item.rejection_reason}
                        </p>
                      )}
                    </div>
                    {item.status === 'rejected' && (
                      <button className="px-4 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors">
                        Revisi
                      </button>
                    )}
                  </div>
                </div>
              );
            })
          )}
        </div>
      )}
    </DashboardLayout>
  );
}