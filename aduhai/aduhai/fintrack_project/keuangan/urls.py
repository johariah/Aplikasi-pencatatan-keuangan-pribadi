from django.urls import path
from . import views

urlpatterns = [
    path('', views.dashboard, name='dashboard'),
    path('login/', views.login_view, name='login'),
    path('register/', views.register_view, name='register'), # Jalur URL Baru
    path('logout/', views.logout_view, name='logout'),
    path('transaksi/',views.transaksi,name='transaksi' ),
    path('export/', views.export_csv, name='export_csv'),
    path('budget/', views.budget, name='budget'),
    path('buat-budget/', views.buat_budget, name='buat_budget'),
    path('budget/', views.target, name='target'),
    path('buat-target/', views.buat_target, name='buat_target'),
    path('laporan/', views.laporan, name='laporan'),
    path('export-pdf/', views.export_pdf, name='export_pdf'),
    path('export-excel/', views.export_excel, name='export_excel'),
]