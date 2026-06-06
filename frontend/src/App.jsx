import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import SustainabilityModule from './pages/SustainabilityModule'
import ResearchModule from './pages/ResearchModule' // File halaman baru lo nanti

function App() {
  return (
    <Router>
      <Routes>
        {/* URL: localhost:5174/ -> Nampilin halaman riset utama */}
        <Route path="/" element={<ResearchModule />} />
        
        {/* URL: localhost:5174/sustainability -> Nampilin modul keberlanjutan lo yang tadi */}
        <Route path="/sustainability" element={<SustainabilityModule />} />
      </Routes>
    </Router>
  )
}

export default App