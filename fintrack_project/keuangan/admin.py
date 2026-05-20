from django.contrib import admin
from .models import Transaksi

class TransaksiAdmin(admin.ModelAdmin):
    # Ini yang MEMAKSA Django menampilkan data dalam bentuk TABEL di browser
    list_display = ('title', 'user', 'amount', 'type', 'category', 'date')
    
    # Menambahkan kotak filter di sebelah kanan tabel
    list_filter = ('type', 'category', 'date')
    
    # Menambahkan kolom pencarian di atas tabel
    search_fields = ('title', 'category')

# Daftarkan model Transaksi dengan konfigurasi tabel di atas
admin.site.register(Transaksi, TransaksiAdmin)