from airflow import DAG
from airflow.operators.python import PythonOperator
from datetime import datetime, timedelta
import requests

def call_login_and_migrate_api():
    # login_url = "https://api.obsrvr.ai/api/login"
    login_url = "http://host.docker.internal:8000/api/login"
    payload = {
        "email": "admin@szgmc.gov.ae",
        "password": "admins"
    }
    headers = {
        "Content-Type": "application/json"
    }

    response = requests.post(login_url, json=payload, headers=headers)
    if response.status_code == 200:
        print(f"Login successful: {response.json()}")
        token = response.json().get("authorisation", {}).get("token")
        print(f"TOKEN: {token}")
        if not token:
            raise ValueError("Token not found in the login response.")
    else:
        raise ValueError(f"Login failed: {response.status_code}, {response.text}")

    # migrate_url = "https://api.obsrvr.ai/api/migrate-fresh-seed"
    migrate_url = "http://host.docker.internal:8000/api/migrate-fresh-seed"
    headers = {
        "Authorization": f"Bearer {token}"
    }

    response = requests.get(migrate_url, headers=headers)
    if response.status_code == 200:
        print(f"Migrate API called successfully: {response.json()}")
    else:
        raise ValueError(f"Failed to call Migrate API: {response.status_code}, {response.text}")

default_args = {
    "owner": "airflow",
    "depends_on_past": False,
    "email_on_failure": False,
    "email_on_retry": False,
    "retries": 1,
    "retry_delay": timedelta(minutes=1),
}

with DAG(
    "migrate_seed_every_10_mn",
    default_args=default_args,
    description="Call login and migrate APIs in a single task every 10 minutes",
    schedule_interval="*/10 * * * *",
    # schedule_interval="0 10,14,18,22 * * *",
    start_date=datetime(2024, 12, 20),
    catchup=False,
    tags=["api", "example"],
) as dag:

    login_and_migrate_task = PythonOperator(
        task_id="call_login_and_migrate_api",
        python_callable=call_login_and_migrate_api,
    )
