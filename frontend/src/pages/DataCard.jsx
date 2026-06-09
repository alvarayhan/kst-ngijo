import { ChevronRight, Clock, CheckCircle, XCircle, Pencil, ThumbsUp, ThumbsDown, Lock } from 'lucide-react';

export function DataCard({ data, viewMode = 'operator', onApprove, onReject, onEdit }) {
  const getStatusConfig = (status) => {
    switch (status) {
      case 'pending':
        return {
          color: 'bg-orange-500',
          bgBadge: 'bg-orange-50',
          textBadge: 'text-orange-700',
          borderBadge: 'border-orange-200',
          icon: <Clock size={14} />,
          label: 'Menunggu Review',
        };
      case 'rejected':
        return {
          color: 'bg-red-500',
          bgBadge: 'bg-red-50',
          textBadge: 'text-red-700',
          borderBadge: 'border-red-200',
          icon: <XCircle size={14} />,
          label: 'Ditolak — Perlu Revisi',
        };
      case 'approved':
        return {
          color: 'bg-green-500',
          bgBadge: 'bg-green-50',
          textBadge: 'text-green-700',
          borderBadge: 'border-green-200',
          icon: <CheckCircle size={14} />,
          label: 'Disetujui',
        };
      default:
        return {
          color: 'bg-gray-400',
          bgBadge: 'bg-gray-50',
          textBadge: 'text-gray-600',
          borderBadge: 'border-gray-200',
          icon: null,
          label: 'Unknown',
        };
    }
  };

  const config = getStatusConfig(data.status);

  return (
    <div className="group bg-white border border-gray-200 rounded-xl flex items-stretch hover:shadow-md transition-shadow overflow-hidden mb-4">
      <div className={`w-1.5 flex-shrink-0 ${config.color}`}></div>

      <div className="flex-1 p-5">
        <div className="flex items-start justify-between">
          {/* Info Utama */}
          <div className="flex items-center gap-4 flex-1">
            <div className={`w-11 h-11 rounded-full border ${config.borderBadge} ${config.textBadge} flex items-center justify-center bg-white shadow-sm flex-shrink-0`}>
              {config.icon}
            </div>
            <div className="flex flex-col gap-0.5 flex-1 min-w-0">
              <div className="flex items-center gap-3">
                <h3 className="text-sm font-bold text-gray-900 truncate">{data.title}</h3>
                <span className="text-xs text-gray-400 flex-shrink-0">ID# {data.id}</span>
              </div>
              {data.submittedBy && (
                <span className="text-xs text-gray-400">oleh {data.submittedBy}</span>
              )}
              <p className="text-sm text-gray-600 line-clamp-1 mt-0.5">{data.description}</p>
              {data.rejectionNote && data.status === 'rejected' && (
                <div className="mt-2 px-3 py-1.5 bg-red-50 border border-red-100 rounded-md">
                  <span className="text-xs font-semibold text-red-700">Catatan Admin: </span>
                  <span className="text-xs text-red-600">{data.rejectionNote}</span>
                </div>
              )}
            </div>
          </div>

          {/* Badge + Actions */}
          <div className="flex flex-col items-end gap-3 ml-6 flex-shrink-0">
            <div className={`flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ${config.bgBadge} ${config.textBadge}`}>
              {config.icon}
              {config.label}
            </div>

            {/* === Tombol Aksi Operator === */}
            {viewMode === 'operator' && data.status === 'rejected' && (
              <button
                onClick={() => onEdit?.(data)}
                className="flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 text-white rounded-lg text-xs font-semibold hover:bg-orange-600 transition-colors shadow-sm"
              >
                <Pencil size={12} />
                Revisi
              </button>
            )}
            {viewMode === 'operator' && data.status === 'pending' && (
              <span className="flex items-center gap-1.5 px-3 py-1.5 text-gray-400 text-xs">
                <Clock size={12} />
                Menunggu...
              </span>
            )}
            {viewMode === 'operator' && data.status === 'approved' && (
              <span className="flex items-center gap-1.5 px-3 py-1.5 text-gray-400 text-xs">
                <Lock size={12} />
                Terkunci
              </span>
            )}

            {/* === Tombol Aksi Admin === */}
            {viewMode === 'admin' && data.status === 'pending' && (
              <div className="flex items-center gap-2">
                <button
                  onClick={() => onApprove?.(data)}
                  className="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-xs font-semibold hover:bg-emerald-600 transition-colors shadow-sm"
                >
                  <ThumbsUp size={12} />
                  Approve
                </button>
                <button
                  onClick={() => onReject?.(data)}
                  className="flex items-center gap-1.5 px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-semibold hover:bg-red-600 transition-colors shadow-sm"
                >
                  <ThumbsDown size={12} />
                  Reject
                </button>
              </div>
            )}
            {viewMode === 'admin' && data.status !== 'pending' && (
              <div className="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-gray-200 transition-colors text-gray-400 cursor-pointer">
                <ChevronRight size={18} />
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}