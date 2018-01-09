package com.irk.cacti;

import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.design.widget.TabLayout;
import android.util.Log;
import android.view.SubMenu;
import android.view.View;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;

import de.hdodenhof.circleimageview.CircleImageView;
import fr.tkeunebr.gravatar.Gravatar;

public class DashboardActivity extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener {
    MainApplication m;

    CircleImageView imgProfilePic, imgDashboardProfilePic;
    TextView txtName, txtPoints, txtDashboardName;
    TabLayout lytTabs;

    Button btnStats, btnQuestions, btnAnswers;
    ImageView imgStats, imgQuestions, imgAnswers;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard);

        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        m = (MainApplication) getApplicationContext();

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(
                this, drawer, toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        drawer.setDrawerListener(toggle);
        toggle.syncState();

        final NavigationView navigationView = (NavigationView) findViewById(R.id.nav_view);
        navigationView.setNavigationItemSelectedListener(this);

        imgProfilePic = (CircleImageView) findViewById(R.id.imgProfilePic);
        imgDashboardProfilePic = (CircleImageView)  navigationView.getHeaderView(0).findViewById(R.id.imgDashboardProfilePic);

        txtDashboardName = (TextView) navigationView.getHeaderView(0).findViewById(R.id.txtDashboardName);
        txtName = (TextView) findViewById(R.id.txtName);
        txtPoints = (TextView) findViewById(R.id.txtPoints);

        btnStats = (Button) findViewById(R.id.btnStats);
        btnQuestions = (Button) findViewById(R.id.btnQuestions);
        btnAnswers = (Button) findViewById(R.id.btnAnswers);

        imgStats = (ImageView) findViewById(R.id.imgStatsHard);
        imgQuestions = (ImageView) findViewById(R.id.imgQuestionsHard);
        imgAnswers = (ImageView) findViewById(R.id.imgAnswersHard);

        btnStats.setBackgroundResource(R.drawable.stats_green);
        btnQuestions.setBackgroundResource(R.drawable.questions_grey);
        btnAnswers.setBackgroundResource(R.drawable.answers_grey);

        imgStats.setVisibility(View.VISIBLE);
        imgQuestions.setVisibility(View.GONE);
        imgAnswers.setVisibility(View.GONE);

        btnStats.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                btnStats.setBackgroundResource(R.drawable.stats_green);
                btnQuestions.setBackgroundResource(R.drawable.questions_grey);
                btnAnswers.setBackgroundResource(R.drawable.answers_grey);

                imgStats.setVisibility(View.VISIBLE);
                imgQuestions.setVisibility(View.GONE);
                imgAnswers.setVisibility(View.GONE);
            }
        });

        btnQuestions.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                btnQuestions.setBackgroundResource(R.drawable.questions_green);
                btnStats.setBackgroundResource(R.drawable.stats_grey);
                btnAnswers.setBackgroundResource(R.drawable.answers_grey);

                imgQuestions.setVisibility(View.VISIBLE);
                imgStats.setVisibility(View.GONE);
                imgAnswers.setVisibility(View.GONE);
            }
        });

        btnAnswers.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                btnAnswers.setBackgroundResource(R.drawable.answers_green);
                btnQuestions.setBackgroundResource(R.drawable.questions_grey);
                btnStats.setBackgroundResource(R.drawable.stats_grey);

                imgAnswers.setVisibility(View.VISIBLE);
                imgQuestions.setVisibility(View.GONE);
                imgStats.setVisibility(View.GONE);
            }
        });

        StringRequest userRequest = new StringRequest(m.SERVER_HOST + "/scripts/GetUser.php?userId=" + m.userId, new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject result = new JSONObject(response);

                    if(!result.getBoolean("success")) {
                        Toast.makeText(DashboardActivity.this, result.getString("message"), Toast.LENGTH_LONG).show();
                    }
                    else {
                        JSONObject user = result.getJSONObject("user");
                        m.name = user.getString("name");
                        m.email = user.getString("email");

                        txtName.setText(m.name);
                        txtDashboardName.setText(m.name);
                        txtPoints.setText(user.getInt("points") + " points");

                        Menu menu = navigationView.getMenu();
                        SubMenu inst = menu.getItem(0).getSubMenu();
                        for(int i = 0; i < user.getJSONArray("institutions").length(); i++) {
                            JSONObject o = user.getJSONArray("institutions").getJSONObject(i);
                            inst.add(o.getString("name"));
                        }

                        final String gravatarURL = Gravatar.init().with(m.email).defaultImage(1).size(100).build();

                        new Thread(new Runnable() {
                            @Override
                            public void run() {
                                try {
                                    URL url = new URL(gravatarURL);
                                    final Bitmap bmp = BitmapFactory.decodeStream(url.openConnection().getInputStream());
                                    runOnUiThread(new Runnable() {
                                        @Override
                                        public void run() {
                                            //imgProfilePic.setBackground(getDrawable(R.drawable.display_rayson_big));
                                            imgProfilePic.setImageBitmap(bmp);
                                            imgDashboardProfilePic.setImageBitmap(bmp);
                                        }
                                    });
                                } catch (MalformedURLException e) {
                                    e.printStackTrace();
                                } catch (IOException e) {
                                    e.printStackTrace();
                                }
                            }
                        }).start();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

            }
        });

        m.requestQueue.add(userRequest);
    }

    @Override
    public void onBackPressed() {
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.dashboard, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @SuppressWarnings("StatementWithEmptyBody")
    @Override
    public boolean onNavigationItemSelected(MenuItem item) {
        // Handle navigation view item clicks here.
        int id = item.getItemId();

        if(id == R.id.itmAddInstitution) {
            Intent addInstitutionIntent = new Intent(DashboardActivity.this, JoinInstitutionActivity.class);
            startActivity(addInstitutionIntent);
        }
        else if(id == R.id.logout) {
            SharedPreferences.Editor editor = PreferenceManager.getDefaultSharedPreferences(DashboardActivity.this).edit();
            editor.remove("userId");
            editor.clear();
            editor.apply();

            Intent mainIntent = new Intent(DashboardActivity.this, MainActivity.class);
            startActivity(mainIntent);
            finish();
        }
        if (id == R.id.nav_manage) {

        } else if (id == 0) {
            Intent questionIntent = new Intent(DashboardActivity.this, QuestionsActivity.class);
            questionIntent.putExtra("institution", item.getTitle().toString());
            startActivity(questionIntent);
        }

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }
}
