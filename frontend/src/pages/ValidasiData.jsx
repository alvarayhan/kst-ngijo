import { useEffect, useState } from 'react';
import { DashboardLayout } from './DashboardLayout';
import { useApiWithAuth } from '../hooks/useApiWithAuth';
import { ShieldCheck, Clock, CheckCircle, XCircle, Loader2, AlertCircle } from 'lucide-react';

const STATUS_CONFIG = {
  pending:  { label: 'Pending',   bg: 'bg-orange-50', border: 'border-orange-200', text: 'text-orange-700', dot: 'bg-orange-400' },
  approved: { label: 'Disetujui', bg: 'bg-green-50',  border: 'border-green-200',  text: 'text-green-700',  dot: 'bg-green-500' },
  rejected: { label: 'Ditolak',   bg: 'bg-red-50',    border: 'border-red-200',    text: 'text-red-700',    dot: 'bg-red-500' },
};

export default function ValidasiData() {
  // ✅ Hook di dalam component
  const { fetchWithAuth } = useApiWithAuth();
  const [dataList, setDataList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filterStatus, setFilterStatus] = useState('all');
  const [actionLoading, setActionLoading] = useState(null);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      // ✅ fetchWithAuth return json langsung, gak perlu .json() lagi
      const json = await fetchWithAuth('/internal/sustainability-data');
      if (!json) return;
      setDataList(json.real_time_sensor_feed || []);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchData(); }, []);

  const handleApprove = async (item) => {
    setActionLoading(item.id);
    try {
      const json = await fetchWithAuth(`/internal/sustainability-data/${item.id}`, {
        method: 'PUT',
        body: JSON.stringify({ status: 'approved' }),
      });
      if (!json) return;
      setDataList((prev) => prev.map((d) => d.id === item.id ? { ...d, status: 'approved' } : d));
    } catch (err) {
      alert('Error: ' + err.message);
    } finally {
      setActionLoading(null);
    }
  };

  const handleReject = async (item) => {
    const reason = prompt(`Alasan penolakan untuk "${item.metric_name}":`);
    if (reason === null) return;

    setActionLoading(item.id);
    try {
      const json = await fetchWithAuth(`/internal/sustainability-data/${item.id}`, {
        method: 'PUT',
        body: JSON.stringify({
          status: 'rejected',
          rejection_reason: reason || 'Data perlu direvisi.',
        }),
      });
      if (!json) return;
      setDataList((prev) => prev.map((d) =>
        d.id === item.id ? { ...d, status: 'rejected', rejection_reason: reason } : d
      ));
    } catch (err) {
      alert('Error: ' + err.message);
    } finally {
      setActionLoading(null);
    }
  };

  const counts = {
    all: dataList.length,
    pending: dataList.filter((d) => d.status === 'pending').length,
    rejected: dataList.filter((d) => d.status === 'rejected').length,
    approved: dataList.filter((d) => d.status === 'approved').length,
  };

  const filteredData =
    filterStatus === 'all' ? dataList : dataList.filter((d) => d.status === filterStatus);

  const filterButtons = [
    { key: 'all', label: 'Semua', count: counts.all },
    { key: 'pending', label: 'Pending', count: counts.pending },
    { key: 'rejected', label: 'Ditolak', count: counts.rejected },
    { key: 'approved', label: 'Disetujui', count: counts.approved },
  ];

  const handleNavigate = (key) => console.log('Navigate to:', key);

  return (
    <DashboardLayout activeKey="validasi" onNavigate={handleNavigate}>
      <div className="flex items-center gap-3 mb-1">
        <ShieldCheck size={24} className="text-emerald-600" />
        <h1 className="text-2xl font-bold text-gray-900">Validasi Data</h1>
      </div>
      <p className="text-sm text-gray-500 mb-6">Review dan validasi data yang dikirim oleh Operator.</p>

      <div className="grid grid-cols-3 gap-4 mb-6">
        <div className="bg-white border border-orange-100 rounded-xl p-4 flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-600"><Clock size={18} /></div>
          <div>
            <p className="text-2xl font-bold text-gray-900">{counts.pending}</p>
            <p className="text-xs text-orange-600 font-semibold">Butuh Review</p>
          </div>
        </div>
        <div className="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600"><XCircle size={18} /></div>
          <div>
            <p className="text-2xl font-bold text-gray-900">{counts.rejected}</p>
            <p className="text-xs text-gray-500">Ditolak</p>
          </div>
        </div>
        <div className="bg-white border border-gray-200 rounded-xl p-4 flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600"><CheckCircle size={18} /></div>
          <div>
            <p className="text-2xl font-bold text-gray-900">{counts.approved}</p>
            <p className="text-xs text-gray-500">Disetujui</p>
          </div>
        </div>
      </div>

      <div className="flex items-center gap-2 mb-6">
        {filterButtons.map((btn) => (
          <button key={btn.key} onClick={() => setFilterStatus(btn.key)}
            className={`px-4 py-1.5 rounded-full text-xs font-semibold transition-colors ${
              filterStatus === btn.key ? 'bg-emerald-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
            }`}>
            {btn.label} ({btn.count})
          </button>
        ))}
      </div>

      {loading && (
        <div className="flex items-center justify-center py-20 gap-3 text-gray-400">
          <Loader2 size={20} className="animate-spin" /> <span className="text-sm">Memuat data...</span>
        </div>
      )}
      {!loading && error && (
        <div className="flex items-center justify-center py-16 gap-3 text-red-500">
          <AlertCircle size={20} /> <span className="text-sm">{error}</span>
        </div>
      )}

      {!loading && !error && (
        <div className="flex flex-col gap-3 max-w-4xl">
          {filteredData.length === 0 ? (
            <div className="text-center py-16 text-gray-400 text-sm bg-white rounded-xl border border-gray-100">
              Tidak ada data dengan filter ini.
            </div>
          ) : (
            filteredData.map((item) => {
              const cfg = STATUS_CONFIG[item.status] || STATUS_CONFIG.pending;
              const isActioning = actionLoading === item.id;
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
                      {item.notes && <p className="text-xs text-gray-400 mt-1">📝 {item.notes}</p>}
                    </div>
                    {item.status === 'pending' && (
                      <div className="flex items-center gap-2 ml-4">
                        <button onClick={() => handleApprove(item)} disabled={isActioning}
                          className="px-4 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors">
                          {isActioning ? '...' : '✓ Approve'}
                        </button>
                        <button onClick={() => handleReject(item)} disabled={isActioning}
                          className="px-4 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                          {isActioning ? '...' : '✕ Reject'}
                        </button>
                      </div>
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