from django.db import models
from django.contrib.auth.models import User

class Transaksi(models.Model):
    JENIS_CHOICES = [
        ('pemasukan', 'Pemasukan'),
        ('pengeluaran', 'Pengeluaran'),
    ]
    
    # Menghubungkan transaksi ke user tertentu. Jika user dihapus, datanya ikut terhapus.
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    title = models.CharField(max_length=200)
    amount = models.DecimalField(max_digits=12, decimal_places=2)
    type = models.CharField(max_length=15, choices=JENIS_CHOICES)
    category = models.CharField(max_length=50)
    date = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"{self.title} - {self.amount}"