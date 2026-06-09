import { useEffect, useState } from 'react'
import { DashboardLayout } from './DashboardLayout'
import { useAuth } from '../context/AuthContext'
import MetricCard from '../components/MetricCard'
import SensorTable from '../components/SensorTable'
import { Leaf, Wifi, WifiOff } from 'lucide-react'

export default function SustainabilityModule() {
  const { user } = useAuth()
  const [dashboardData, setDashboardData] = useState(null)
  const [errorLog, setErrorLog] = useState(null)

  useEffect(() => {
    // Nanti ganti pake token dari user.token yang udah disimpen di AuthContext
    const token = user?.token || ''

    fetch('http://localhost/api/internal/sustainability-data', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })
    .then(res => {
      if (!res.ok) throw new Error(`Token invalid / Expired (Status: ${res.status})`)
      return res.json()
    })
    .then(data => setDashboardData(data))
    .catch(err => setErrorLog(err.message))
  }, [user])

  const handleNavigate = (key) => {
    console.log('Navigate to:', key)
  }

  return (
    <DashboardLayout activeKey="keberlanjutan" onNavigate={handleNavigate}>
      {/* Header */}
      <div className="flex items-start justify-between mb-6">
        <div>
          <div className="flex items-center gap-2 text-xs mb-1">
            <span className="text-emerald-700 font-semibold">RUANG KONTROL</span>
          </div>
          <h1 className="text-2xl font-bold text-gray-900 mb-1">Modul Keberlanjutan</h1>
          <p className="text-sm text-gray-500 max-w-xl">
            Pemantauan secara real-time terhadap jejak ekologis dan efisiensi sumber daya Taman Sains Hijau.
          </p>
        </div>
        <div className="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-200">
          {dashboardData ? (
            <Wifi size={14} className="text-emerald-500" />
          ) : (
            <WifiOff size={14} className="text-red-500" />
          )}
          <span className="text-xs font-semibold text-gray-600">
            {dashboardData ? 'Connected' : 'Disconnected'}
          </span>
        </div>
      </div>

      {/* Error Banner */}
      {errorLog && (
        <div className="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl text-sm font-medium mb-6">
          🛑 Error: {errorLog}
        </div>
      )}

      {/* Data Content */}
      {dashboardData && (
        <>
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

          <SensorTable feeds={dashboardData.real_time_sensor_feed} />
        </>
      )}

      {/* Loading State */}
      {!dashboardData && !errorLog && (
        <div className="flex items-center justify-center py-20">
          <div className="flex flex-col items-center gap-3">
            <div className="w-8 h-8 border-2 border-emerald-200 border-t-emerald-600 rounded-full animate-spin"></div>
            <p className="text-sm text-gray-400">Memuat data sensor...</p>
          </div>
        </div>
      )}
    </DashboardLayout>
  )
}