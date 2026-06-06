import React from 'react'

export default function SensorTable({ feeds }) {
  const getStatusBadge = (notes) => {
    const text = notes ? notes.toUpperCase() : '';
    if (text.includes('CRITICAL')) {
      return <span className="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 animate-pulse">CRITICAL</span>;
    }
    if (text.includes('ATTENTION')) {
      return <span className="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">ATTENTION</span>;
    }
    return <span className="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">OPTIMAL</span>;
  };

  return (
    <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
      <div className="p-5 border-b border-slate-200 bg-slate-50/50">
        <h3 className="text-lg font-bold text-slate-900">Real Time Sensor Feed</h3>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-left text-sm border-collapse">
          <thead>
            <tr className="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold">
              <th className="p-4 px-6">Tanggal Record</th>
              <th className="p-4 px-6">Kategori</th>
              <th className="p-4 px-6">Detail Sensor & Lokasi Kawasan</th>
              <th className="p-4 px-6 text-right">Nilai Bacaan</th>
              <th className="p-4 px-6 text-center">Target</th>
              <th className="p-4 px-6 text-center">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {feeds.map((sensor) => (
              <tr key={sensor.id} className="hover:bg-slate-50/80 transition-colors">
                <td className="p-4 px-6 text-slate-600 font-medium">
                  {new Date(sensor.record_date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}
                </td>
                <td className="p-4 px-6">
                  <span className="text-[11px] font-bold px-2 py-1 rounded bg-slate-100 text-slate-700 uppercase tracking-wide">
                    {sensor.category}
                  </span>
                </td>
                <td className="p-4 px-6 font-semibold text-slate-900 max-w-xs truncate">{sensor.metric_name}</td>
                <td className="p-4 px-6 text-right font-bold text-slate-900 text-base">
                  {sensor.value} <span className="text-xs font-normal text-slate-400">{sensor.unit}</span>
                </td>
                <td className="p-4 px-6 text-center text-slate-400">
                  {sensor.target_value ? `${sensor.target_value} ${sensor.unit}` : '-'}
                </td>
                <td className="p-4 px-6 text-center">{getStatusBadge(sensor.notes)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}