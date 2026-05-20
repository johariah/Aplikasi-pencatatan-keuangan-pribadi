from django.urls import path
from . import views

urlpatterns = [
    path('', views.home, name='home'),
    path('menu/', views.menu, name='menu'),
    path('checkout/', views.checkout, name='checkout'),
    path('payment/', views.payment, name='payment'),
    path('payment/bank/', views.payment_bank, name='payment_bank'),
    path('payment/credit/', views.payment_credit, name='payment_credit'),
    path('payment/ewallet/', views.payment_ewallet, name='payment_ewallet'),
    path('payment/cod/', views.payment_cod, name='payment_cod'),
    path('payment/success/', views.payment_success, name='payment_success'),
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),
]
