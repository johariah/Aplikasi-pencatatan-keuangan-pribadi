from django.contrib import admin
from .models import Transaksi, Budget, SavingGoal


class TransaksiAdmin(admin.ModelAdmin):
    list_display = (
        'title',
        'user',
        'amount',
        'type',
        'category',
        'date'
    )

    list_filter = (
        'type',
        'category',
        'date'
    )

    search_fields = (
        'title',
        'category'
    )


class BudgetAdmin(admin.ModelAdmin):
    list_display = (
        'user',
        'category',
        'amount',
        'month',
        'year'
    )

    list_filter = (
        'month',
        'year',
        'category'
    )


class SavingGoalAdmin(admin.ModelAdmin):
    list_display = (
        'user',
        'title',
        'target_amount',
        'current_amount'
    )


admin.site.register(
    Transaksi,
    TransaksiAdmin
)

admin.site.register(
    Budget,
    BudgetAdmin
)

admin.site.register(
    SavingGoal,
    SavingGoalAdmin
)