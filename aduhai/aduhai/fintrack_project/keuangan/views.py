from django.shortcuts import render
from django.shortcuts import redirect
from django.contrib.auth import login
from django.contrib.auth import logout
from django.contrib.auth import authenticate
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import User
from .models import Budget
from django.db.models import Sum
from django.http import HttpResponse

from django.utils import timezone

from reportlab.pdfgen import canvas
from openpyxl import Workbook

import csv
import json

from .models import (
    Transaksi,
    Budget,
    SavingGoal
)


# =====================================
# DASHBOARD
# =====================================

@login_required(login_url='login')
def dashboard(request):

    bulan_sekarang = timezone.now().month
    tahun_sekarang = timezone.now().year

    bulan_dipilih = int(
        request.GET.get(
            'bulan',
            bulan_sekarang
        )
    )

    tahun_dipilih = int(
        request.GET.get(
            'tahun',
            tahun_sekarang
        )
    )

    transaksi_user = Transaksi.objects.filter(
        user=request.user,
        date__month=bulan_dipilih,
        date__year=tahun_dipilih
    ).order_by('-date')

    if request.method == 'POST':

        title = request.POST.get('title')
        amount = request.POST.get('amount')
        jenis = request.POST.get('type')
        category = request.POST.get('category')

        Transaksi.objects.create(
            user=request.user,
            title=title,
            amount=amount,
            type=jenis,
            category=category
        )

        return redirect('dashboard')

    total_masuk = transaksi_user.filter(
        type='pemasukan'
    ).aggregate(
        Sum('amount')
    )['amount__sum'] or 0

    total_keluar = transaksi_user.filter(
        type='pengeluaran'
    ).aggregate(
        Sum('amount')
    )['amount__sum'] or 0

    saldo = total_masuk - total_keluar

    # ======================
    # DONUT CHART
    # ======================

    kategori_pengeluaran = transaksi_user.filter(
        type='pengeluaran'
    ).values(
        'category'
    ).annotate(
        total=Sum('amount')
    )

    labels = []
    values = []

    for item in kategori_pengeluaran:

        labels.append(
            item['category']
        )

        values.append(
            float(item['total'])
        )

    chart_labels = json.dumps(labels)
    chart_values = json.dumps(values)

    # ======================
    # BUDGET
    # ======================

    budgets = Budget.objects.filter(
        user=request.user,
        month=bulan_dipilih,
        year=tahun_dipilih
    )

    budget_warning = []

    for budget in budgets:

        total_kategori = transaksi_user.filter(
            type='pengeluaran',
            category=budget.category
        ).aggregate(
            Sum('amount')
        )['amount__sum'] or 0

        persen = 0

        if budget.amount > 0:

            persen = round(
                (
                    float(total_kategori)
                    /
                    float(budget.amount)
                ) * 100,
                1
            )

        if persen >= 80:

            budget_warning.append({
                'kategori': budget.category,
                'persen': persen
            })

    # ======================
    # SAVING GOAL
    # ======================

    goal = SavingGoal.objects.filter(
        user=request.user
    ).first()

    progress = 0

    if goal:
        progress = goal.progress()

    # ======================
    # GRAFIK TAHUNAN
    # ======================

    income_year = []
    expense_year = []

    for month in range(1, 13):

        income = Transaksi.objects.filter(
            user=request.user,
            type='pemasukan',
            date__month=month,
            date__year=tahun_dipilih
        ).aggregate(
            Sum('amount')
        )['amount__sum'] or 0

        expense = Transaksi.objects.filter(
            user=request.user,
            type='pengeluaran',
            date__month=month,
            date__year=tahun_dipilih
        ).aggregate(
            Sum('amount')
        )['amount__sum'] or 0

        income_year.append(
            float(income)
        )

        expense_year.append(
            float(expense)
        )

    context = {

        'transaksi': transaksi_user,

        'saldo': saldo,

        'total_masuk': total_masuk,

        'total_keluar': total_keluar,

        'bulan_dipilih': bulan_dipilih,

        'tahun_dipilih': tahun_dipilih,

        'chart_labels': chart_labels,

        'chart_values': chart_values,

        'budgets': budgets,

        'budget_warning': budget_warning,

        'goal': goal,

        'progress': progress,

        'income_year': json.dumps(
            income_year
        ),

        'expense_year': json.dumps(
            expense_year
        ),
    }

    return render(
        request,
        'dashboard.html',
        context
    )


# =====================================
# BUDGET
# =====================================

@login_required
def buat_budget(request):

    if request.method == 'POST':

        Budget.objects.create(
            user=request.user,
            category=request.POST['category'],
            amount=request.POST['amount'],
            month=request.POST['month'],
            year=request.POST['year']
        )

    return redirect('dashboard')


# =====================================
# SAVING GOAL
# =====================================

@login_required
def buat_target(request):

    if request.method == 'POST':

        SavingGoal.objects.create(
            user=request.user,
            title=request.POST['title'],
            target_amount=request.POST['target_amount'],
            current_amount=request.POST['current_amount']
        )

    return redirect('dashboard')


# =====================================
# LOGIN
# =====================================

def login_view(request):

    if request.user.is_authenticated:
        return redirect('dashboard')

    if request.method == 'POST':

        username = request.POST.get(
            'username'
        )

        password = request.POST.get(
            'password'
        )

        user = authenticate(
            request,
            username=username,
            password=password
        )

        if user:

            login(
                request,
                user
            )

            return redirect(
                'dashboard'
            )

        return render(
            request,
            'login.html',
            {
                'error':
                'Username atau password salah'
            }
        )

    return render(
        request,
        'login.html'
    )


# =====================================
# REGISTER
# =====================================

def register_view(request):

    if request.method == 'POST':

        username = request.POST['username']

        email = request.POST['email']

        password = request.POST['password']

        konfirmasi = request.POST[
            'password_konfirmasi'
        ]

        if password != konfirmasi:

            return render(
                request,
                'register.html',
                {
                    'error':
                    'Password tidak cocok'
                }
            )

        user = User.objects.create_user(
            username=username,
            email=email,
            password=password
        )

        login(
            request,
            user
        )

        return redirect(
            'dashboard'
        )

    return render(
        request,
        'register.html'
    )


# =====================================
# LOGOUT
# =====================================

def logout_view(request):

    logout(request)

    return redirect(
        'login'
    )


# =====================================
# HAPUS TRANSAKSI
# =====================================

@login_required
def hapus_transaksi(
    request,
    pk
):

    transaksi = Transaksi.objects.filter(
        id=pk,
        user=request.user
    )

    transaksi.delete()

    return redirect(
        'dashboard'
    )


# =====================================
# EXPORT CSV
# =====================================

@login_required
def export_csv(request):

    response = HttpResponse(
        content_type='text/csv'
    )

    response[
        'Content-Disposition'
    ] = 'attachment; filename=laporan.csv'

    writer = csv.writer(
        response
    )

    writer.writerow([
        'Tanggal',
        'Judul',
        'Kategori',
        'Jenis',
        'Jumlah'
    ])

    transaksi = Transaksi.objects.filter(
        user=request.user
    )

    for t in transaksi:

        writer.writerow([
            t.date,
            t.title,
            t.category,
            t.type,
            t.amount
        ])

    return response


# =====================================
# EXPORT PDF
# =====================================

@login_required
def export_pdf(request):

    response = HttpResponse(
        content_type='application/pdf'
    )

    response[
        'Content-Disposition'
    ] = 'attachment; filename=laporan.pdf'

    pdf = canvas.Canvas(
        response
    )

    pdf.drawString(
        100,
        800,
        "Laporan Keuangan FinTrack"
    )

    transaksi = Transaksi.objects.filter(
        user=request.user
    )

    y = 760

    for t in transaksi:

        pdf.drawString(
            50,
            y,
            f"{t.title} - Rp {t.amount}"
        )

        y -= 20

    pdf.save()

    return response


# =====================================
# EXPORT EXCEL
# =====================================

@login_required
def export_excel(request):

    workbook = Workbook()

    sheet = workbook.active

    sheet.title = "Laporan"

    sheet.append([
        'Tanggal',
        'Judul',
        'Kategori',
        'Jenis',
        'Jumlah'
    ])

    transaksi = Transaksi.objects.filter(
        user=request.user
    )

    for t in transaksi:

        sheet.append([
            str(t.date),
            t.title,
            t.category,
            t.type,
            float(t.amount)
        ])

    response = HttpResponse(
        content_type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    )

    response[
        'Content-Disposition'
    ] = 'attachment; filename=laporan.xlsx'

    workbook.save(response)

    return response
    
@login_required
def transaksi(request):

    data_transaksi = Transaksi.objects.filter(
        user=request.user
    ).order_by('-date')

    return render(
        request,
        'transaksi.html',
        {
            'transaksi': data_transaksi
        }
    )

@login_required
def budget(request):
    budgets = Budget.objects.filter(
        user=request.user
    )

    return render(
        request,
        'budget.html',
        {
            'budgets': budgets
        }
    )

@login_required
def buat_budget(request):
    if request.method == 'POST':
        category = request.POST.get('category')
        amount = request.POST.get('amount')
        month = request.POST.get('month')
        year = request.POST.get('year')

        Budget.objects.create(
            user=request.user,
            category=category,
            amount=amount,
            month=month,
            year=year
        )

    return redirect('budget')

@login_required
def target(request):

    goals = SavingGoal.objects.filter(user=request.user)

    for goal in goals:
        if goal.target_amount > 0:
            goal.progress = int(
                (goal.current_amount / goal.target_amount) * 100
            )
        else:
            goal.progress = 0

    return render(
        request,
        'target.html',
        {
            'goals': goals
        }
    )


@login_required
def laporan(request):

    transaksi = Transaksi.objects.filter(
        user=request.user
    )

    total_masuk = transaksi.filter(
        type='pemasukan'
    ).aggregate(
        Sum('amount')
    )['amount__sum'] or 0

    total_keluar = transaksi.filter(
        type='pengeluaran'
    ).aggregate(
        Sum('amount')
    )['amount__sum'] or 0

    saldo = total_masuk - total_keluar

    return render(
        request,
        'laporan.html',
        {
            'transaksi': transaksi,
            'total_masuk': total_masuk,
            'total_keluar': total_keluar,
            'saldo': saldo,
            'chart_labels': [],
            'chart_values': [],
        }
    )