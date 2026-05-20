from django.urls import path
from . import views

urlpatterns = [
    path('', views.dashboard, name='dashboard'),
    path('login/', views.login_view, name='login'),
    path('register/', views.register_view, name='register'), # Jalur URL Baru
    path('logout/', views.logout_view, name='logout'),
    path('hapus/<int:pk>/', views.hapus_transaksi, name='hapus_transaksi'),
    path('export/', views.export_csv, name='export_csv'),
]