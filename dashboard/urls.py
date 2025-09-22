from django.urls import path
from . import views

urlpatterns = [
    path('', views.home, name='home'),       # http://127.0.0.1:8000/
    path('about/', views.about, name='about'),  # http://127.0.0.1:8000/about/
]
