import React from 'react'

export default function MetricCard({ title, value, unit, icon, footer }) {
  return (
    <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 transition-all hover:shadow-md">
      <div className="text-sm font-semibold text-slate-500 mb-2 uppercase tracking-wider">{title}</div>
      <div className="flex items-baseline gap-2">
        <span className="text-4xl font-extrabold text-slate-900">{value}</span>
        <span className="text-base font-bold text-emerald-500">{unit}</span>
      </div>
      <div className="text-xs text-slate-400 mt-3 flex items-center gap-1">
        {icon} {footer}
      </div>
    </div>
  )
}