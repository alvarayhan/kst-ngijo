import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import ProtectedRoute from './components/ProtectedRoute'
import LoginPage from './pages/LoginPage'
import ExecutiveDashboard from './pages/ExecutiveDashboard'
import ResearchModule from './pages/ResearchModule'
import SustainabilityModule from './pages/SustainabilityModule'
import ValidasiData from './pages/ValidasiData'
import InputData from './pages/InputData'
import RiwayatRevisi from './pages/RiwayatRevisi'

function App() {
  return (
    <Router>
      <Routes>
        {/* Public */}
        <Route path="/login" element={<LoginPage />} />

        {/* Shared — Admin & Operator bisa akses */}
        <Route path="/dashboard" element={<ProtectedRoute><ExecutiveDashboard /></ProtectedRoute>} />
        <Route path="/research" element={<ProtectedRoute><ResearchModule /></ProtectedRoute>} />
        <Route path="/sustainability" element={<ProtectedRoute><SustainabilityModule /></ProtectedRoute>} />

        {/* Admin Only */}
        <Route path="/validasi" element={<ProtectedRoute allowedRoles={['admin']}><ValidasiData /></ProtectedRoute>} />

        {/* Operator Only */}
        <Route path="/input" element={<ProtectedRoute allowedRoles={['operator']}><InputData /></ProtectedRoute>} />
        <Route path="/riwayat" element={<ProtectedRoute allowedRoles={['operator']}><RiwayatRevisi /></ProtectedRoute>} />

        {/* Fallback */}
        <Route path="/" element={<LoginPage />} />
      </Routes>
    </Router>
  )
}

export default App