FROM python:3.12-slim
RUN pip install --no-cache-dir "psycopg[binary]==3.*"
WORKDIR /app
COPY metrics_build.py /app/metrics_build.py
ENTRYPOINT ["python", "/app/metrics_build.py"]
