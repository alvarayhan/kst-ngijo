export default function ResearchModule() {
  return (
    <div className="p-10 bg-slate-50 min-h-screen flex flex-col justify-center items-center">
      <h1 className="text-4xl font-black text-slate-900 mb-2">Research & Innovation Module (Page 1) 🔬</h1>
      <p className="text-slate-500 mb-6">Ini halaman utama riset. Di bawah ini tombol buat loncat ke modul keberlanjutan lo, Va.</p>
      
      <a href="/sustainability" className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition-all">
        Buka Dashboard Keberlanjutan ➔
      </a>
    </div>
  )
}