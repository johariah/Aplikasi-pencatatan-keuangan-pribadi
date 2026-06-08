from django.db import models
from django.contrib.auth.models import User


class Transaksi(models.Model):

    JENIS_CHOICES = [
        ('pemasukan', 'Pemasukan'),
        ('pengeluaran', 'Pengeluaran'),
    ]

    user = models.ForeignKey(
        User,
        on_delete=models.CASCADE
    )

    title = models.CharField(
        max_length=200
    )

    amount = models.DecimalField(
        max_digits=12,
        decimal_places=2
    )

    type = models.CharField(
        max_length=15,
        choices=JENIS_CHOICES
    )

    category = models.CharField(
        max_length=50
    )

    date = models.DateTimeField(
        auto_now_add=True
    )

    def __str__(self):
        return self.title


class Budget(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    category = models.CharField(max_length=100)
    amount = models.DecimalField(max_digits=15, decimal_places=2)
    month = models.IntegerField()
    year = models.IntegerField()


class SavingGoal(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE)
    title = models.CharField(max_length=200)
    target_amount = models.DecimalField(max_digits=15, decimal_places=2)
    current_amount = models.DecimalField(max_digits=15, decimal_places=2)

    def progress(self):
        if self.target_amount > 0:
            return round((self.current_amount / self.target_amount) * 100, 1)
        return 0