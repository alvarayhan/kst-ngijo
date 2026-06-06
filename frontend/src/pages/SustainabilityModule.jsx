import { useEffect, useState } from 'react'
import MetricCard from '../components/MetricCard'
import SensorTable from '../components/SensorTable'

export default function SustainabilityModule() {
  const [dashboardData, setDashboardData] = useState(null)
  const [errorLog, setErrorLog] = useState(null)

  useEffect(() => {
    const token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzc5NzA4OTg2LCJleHAiOjE3Nzk3MTI1ODYsIm5iZiI6MTc3OTcwODk4NiwianRpIjoiU1ZMbUpsZnByRENmNXg5SiIsInN1YiI6IjEiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.EKpaWfopqz6fA536ZKovOOdVIIUe0D8xbf0WZp4HUiA"; // Token seger Postman lo

    fetch('http://localhost/api/internal/sustainability-data', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })
    .then(res => {
      if (!res.ok) throw new Error(`Token invalid / Expired (Status: ${res.status})`);
      return res.json();
    })
    .then(data => setDashboardData(data))
    .catch(err => setErrorLog(err.message))
  }, [])

  return (
    <div className="p-10 bg-slate-50 min-h-screen text-slate-800 font-sans">
      {/* HEADER */}
      <div className="flex justify-between items-center mb-8">
        <div>
          <h1 className="text-3xl font-extrabold text-slate-900 tracking-tight mb-1">
            Modul Riset & Inovasi: Keberlanjutan KST Ngijo 🌿
          </h1>
          <p className="text-slate-500 text-sm">
            Pemantauan metrik lingkungan kawasan dan IoT sensor feed secara real-time.
          </p>
        </div>
        <div className="flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
          <span className={`w-2.5 h-2.5 rounded-full ${dashboardData ? 'bg-emerald-500 animate-ping' : 'bg-rose-500'}`}></span>
          <span className="text-xs font-bold text-slate-700">
            {dashboardData ? "WSL Native Connected (5174)" : "Disconnected"}
          </span>
        </div>
      </div>

      {errorLog && (
        <div className="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl font-medium mb-6">
          🛑 Forensic Alert: {errorLog}. Ambil token baru di Postman!
        </div>
      )}

      {dashboardData && (
        <>
          {/* GRID METRICS */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <MetricCard 
              title="Total Renewable Energy" 
              value={dashboardData.dashboard_summary.total_renewable_energy.value} 
              unit={dashboardData.dashboard_summary.total_renewable_energy.unit}
              icon="⚡"
              footer="Bersumber dari Solar Array & Biomass"
            />
            <MetricCard 
              title="Water Recycling Rate" 
              value={dashboardData.dashboard_summary.water_recycling_rate.value} 
              unit={dashboardData.dashboard_summary.water_recycling_rate.unit}
              icon="💧"
              footer="Stasiun Daur Ulang Sektor Barat"
            />
            <MetricCard 
              title="Waste Processed" 
              value={dashboardData.dashboard_summary.waste_processed.value} 
              unit={dashboardData.dashboard_summary.waste_processed.unit}
              icon="♻️"
              footer="Sistem Konveyor Otomatis"
            />
          </div>

          {/* SENSOR TABLE COMPONENT */}
          <SensorTable feeds={dashboardData.real_time_sensor_feed} />
        </>
      )}
    </div>
  )
}