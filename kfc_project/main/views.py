from django.shortcuts import render, redirect
from django.contrib.auth import authenticate, login, logout
from django.contrib import messages
from django.http import HttpResponseRedirect
from django.urls import reverse
from django.contrib.admin.views.decorators import staff_member_required
from django.contrib.auth.decorators import login_required
from .models import MenuItem, Order, OrderItem  # kamu perlu import Order dan OrderItem juga
from .models import CartItem, Order, OrderItem


def home(request):
    return render(request, 'main/home.html')

def menu(request):
    items = MenuItem.objects.all()
    return render(request, 'main/menu.html', {'items': items})

def checkout(request):
    item = request.GET.get('item')
    price = request.GET.get('price')
    return render(request, 'main/checkout.html', {'item': item, 'price': price})

def payment(request):
    return render(request, 'main/payment.html')

def payment_bank(request):
    return render(request, 'main/payment_bank.html')

def payment_credit(request):
    return render(request, 'main/payment_credit.html')

def payment_ewallet(request):
    return render(request, 'main/payment_ewallet.html')

def payment_cod(request):
    return render(request, 'main/payment_cod.html')

def payment_success(request):
    return render(request, 'main/payment_success.html')

def login_view(request):
    # logika login kamu di sini
    return render(request, 'main/login.html')

def login_view(request):
    if request.method == 'POST':
        username = request.POST.get('username')
        password = request.POST.get('password')
        user = authenticate(request, username=username, password=password)

        if user is not None:
            login(request, user)
            return redirect('home')  # langsung ke halaman home
        else:
            messages.error(request, 'Username atau password salah.')

    return render(request, 'main/login.html')


def logout_view(request):
    # logika logout kamu di sini
    return redirect('home')